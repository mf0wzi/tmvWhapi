<?php

namespace Tmv\WhatsApi\Client;

use Tmv\WhatsApi\Entity\Phone;
use Tmv\WhatsApi\Exception\IncompleteMessageException;
use Tmv\WhatsApi\Exception\RuntimeException;
use Tmv\WhatsApi\Message\Action;
use Tmv\WhatsApi\Message\Event\ReceivedNodeEvent;
use Tmv\WhatsApi\Message\MessageQueue;
use Tmv\WhatsApi\Message\Node\Listener\MessageListener;
use Tmv\WhatsApi\Message\Node\Listener\SuccessListener;
use Tmv\WhatsApi\Message\Node\Listener\ChallengeListener;
use Tmv\WhatsApi\Message\Node\NodeFactory;
use Tmv\WhatsApi\Message\Node\NodeInterface;
use Tmv\WhatsApi\Protocol\BinTree\NodeReader;
use Tmv\WhatsApi\Protocol\BinTree\NodeWriter;
use Tmv\WhatsApi\Protocol\Dictionary;
use Tmv\WhatsApi\Protocol\KeyStream;
use Tmv\WhatsApi\Protocol\RC4;
use Tmv\WhatsApi\Service\LocalizationService;
use Tmv\WhatsApi\Service\ProtocolService;
use Zend\EventManager\EventManager;

/**
 * Class Client
 * @package Tmv\WhatsApi\Client
 */
class Client
{

    const PORT = 443; // The port of the WhatsApp server.
    const TIMEOUT_SEC = 2; // The timeout for the connection with the WhatsApp servers.
    const TIMEOUT_USEC = 0; //
    const WHATSAPP_CHECK_HOST = 'v.whatsapp.net/v2/exist'; // The check credentials host.
    const WHATSAPP_GROUP_SERVER = 'g.us'; // The Group server hostname
    const WHATSAPP_HOST = 'c.whatsapp.net'; // The hostname of the WhatsApp server.
    const WHATSAPP_REGISTER_HOST = 'v.whatsapp.net/v2/register'; // The register code host.
    const WHATSAPP_REQUEST_HOST = 'v.whatsapp.net/v2/code'; // The request code host.
    const WHATSAPP_SERVER = 's.whatsapp.net'; // The hostname used to login/send messages.
    const WHATSAPP_UPLOAD_HOST = 'https://mms.whatsapp.net/client/iphone/upload.php'; // The upload host.
    const WHATSAPP_DEVICE = 'Android'; // The device name.
    const WHATSAPP_VER = '2.11.134'; // The WhatsApp version.
    const WHATSAPP_USER_AGENT = 'WhatsApp/2.11.134 Android/4.3 Device/GalaxyS3'; // User agent used in request/registration code.

    /**
     * @var bool
     */
    protected $connected = false;
    /**
     * @var EventManager
     */
    protected $eventManager;
    /**
     * @var resource
     */
    protected $socket;
    /**
     * @var NodeWriter
     */
    protected $nodeWriter;
    /**
     * @var NodeReader
     */
    protected $nodeReader;
    /**
     * @var string
     */
    protected $identity;
    /**
     * @var Phone
     */
    protected $phone;
    /**
     * @var string
     */
    protected $nickname;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var LocalizationService
     */
    protected $localizationService;
    /**
     * @var ProtocolService
     */
    protected $protocolService;

    /**
     * @var string
     */
    protected $challengeData;
    /**
     * A buffer
     * @var string
     */
    protected $incompleteMessage;
    /**
     * Instances of the KeyStream class.
     * @var KeyStream
     */
    protected $inputKey;
    /**
     * Instances of the KeyStream class.
     * @var KeyStream
     */
    protected $outputKey;
    /**
     * @var MessageQueue
     */
    protected $messageQueue;

    /**
     * @var int
     */
    protected $lastMessageIdSent;

    /**
     * @var int
     */
    protected $messageCounter = 0;

    /**
     * @var string
     */
    protected $challengeDataFilepath;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @param  \Tmv\WhatsApi\Message\Node\NodeFactory $nodeFactory
     * @return $this
     */
    public function setNodeFactory($nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;

        return $this;
    }

    /**
     * @return \Tmv\WhatsApi\Message\Node\NodeFactory
     */
    public function getNodeFactory()
    {
        if (!$this->nodeFactory) {
            $this->nodeFactory = new NodeFactory();
        }

        return $this->nodeFactory;
    }

    /**
     * Default class constructor.
     *
     * @param string $phone
     *                         The user phone number including the country code without '+' or '00'.
     * @param string $identity
     *                         The Device Identity token. Obtained during registration with this API
     *                         or using Missvenom to sniff from your phone.
     * @param string $nickname
     *                         The user name.
     */
    public function __construct($phone, $identity, $nickname)
    {

        $this->getEventManager()->attachAggregate(new ChallengeListener(), 9990);
        $this->getEventManager()->attachAggregate(new SuccessListener(), 9980);
        $this->getEventManager()->attachAggregate(new MessageListener(), 9970);

        if (!($phone instanceof Phone)) {
            $phone = new Phone($phone);
        }
        $phone = $this->getLocalizationService()->dissectPhone($phone);
        $dict = new Dictionary();
        $this->nodeWriter = new NodeWriter($dict);
        $this->nodeReader = new NodeReader($dict);
        $this->phone = $phone;
        if (!$this->checkIdentity($identity)) {
            //compute sha identity hash
            $this->identity = $this->buildIdentity($identity);
        } else {
            //use provided identity hash
            $this->identity = $identity;
        }
        $this->nickname = $nickname;
        $this->setConnected(false);
    }

    /**
     * Get the event manager
     *
     * @param EventManager $manager
     *
     * @return EventManager
     */
    public function getEventManager(EventManager $manager = null)
    {
        if (null !== $manager) {
            $this->eventManager = $manager;
        } elseif (null === $this->eventManager) {
            $this->eventManager = new EventManager(__CLASS__);
        }

        return $this->eventManager;
    }

    /**
     * Check if account credentials are valid.
     *
     * WARNING: WhatsApp now changes your password everytime you use this.
     * Make sure you update your config file if the output informs about
     * a password change.
     *
     * @return object
     *                An object with server response.
     *                - status: Account status.
     *                - login: Phone number with country code.
     *                - pw: Account password.
     *                - type: Type of account.
     *                - expiration: Expiration date in UNIX TimeStamp.
     *                - kind: Kind of account.
     *                - price: Formatted price of account.
     *                - cost: Decimal amount of account.
     *                - currency: Currency price of account.
     *                - price_expiration: Price expiration in UNIX TimeStamp.
     *
     * @throws RuntimeException
     */
    public function checkCredentials()
    {
        $phone = $this->phone;
        // Build the url.
        $host = 'https://' . static::WHATSAPP_CHECK_HOST;
        $query = array(
            'cc' => $phone->getCc(),
            'in' => $phone->getPhone(),
            'id' => $this->identity,
            'c' => 'cookie',
        );

        $response = $this->getResponse($host, $query);

        if (isset($response['status']) && $response['status'] != 'ok') {
            return $response;
        }

        throw new RuntimeException("Invalid credentials");
    }

    /**
     * Register account on WhatsApp using the provided code.
     *
     * @param integer $code
     *                      Numeric code value provided on requestCode().
     *
     * @return object
     *                An object with server response.
     *                - status: Account status.
     *                - login: Phone number with country code.
     *                - pw: Account password.
     *                - type: Type of account.
     *                - expiration: Expiration date in UNIX TimeStamp.
     *                - kind: Kind of account.
     *                - price: Formatted price of account.
     *                - cost: Decimal amount of account.
     *                - currency: Currency price of account.
     *                - price_expiration: Price expiration in UNIX TimeStamp.
     *
     * @throws RuntimeException
     */
    public function codeRegister($code)
    {
        $phone = $this->phone;
        // Build the url.
        $host = 'https://' . static::WHATSAPP_REGISTER_HOST;
        $query = array(
            'cc' => $phone->getCc(),
            'in' => $phone->getPhone(),
            'id' => $this->identity,
            'code' => $code,
            'c' => 'cookie',
        );

        $response = $this->getResponse($host, $query);

        if ($response['status'] != 'ok') {
            throw new RuntimeException('An error occurred registering the registration code from WhatsApp');
        }

        return $response;
    }

    /**
     * Request a registration code from WhatsApp.
     *
     * @param string $method
     *                            Accepts only 'sms' or 'voice' as a value.
     * @param string $countryCode
     *                            ISO Country Code, 2 Digit.
     * @param string $langCode
     *                            ISO 639-1 Language Code: two-letter codes.
     *
     * @return object
     *                An object with server response.
     *                - status: Status of the request (sent/fail).
     *                - length: Registration code lenght.
     *                - method: Used method.
     *                - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
     *                - param: The missing_param/bad_param.
     *                - retry_after: Waiting time before requesting a new code.
     *
     * @throws RuntimeException
     */
    public function codeRequest($method = 'sms', $countryCode = null, $langCode = null)
    {
        $phone = $this->phone;
        if ($countryCode == null && $phone->getIso3166() != '') {
            $countryCode = $phone->getIso3166();
        }
        if ($countryCode == null) {
            $countryCode = 'US';
        }
        if ($langCode == null && $phone->getIso639() != '') {
            $langCode = $phone->getIso639();
        }
        if ($langCode == null) {
            $langCode = 'en';
        }

        // Build the token.
        $token = $this->getProtocolService()->generateRequestToken($phone->getCountry(), $phone);

        // Build the url.
        $host = 'https://' . static::WHATSAPP_REQUEST_HOST;
        $query = array(
            'cc' => $phone->getCc(),
            'in' => $phone->getPhone(),
            'to' => $phone->getPhoneNumber(),
            'lg' => $langCode,
            'lc' => $countryCode,
            'method' => $method,
            'mcc' => $phone->getMcc(),
            'mnc' => '001',
            'token' => urlencode($token),
            'id' => $this->identity,
        );

        $this->getEventManager()->trigger('debug.message', $this, array('message' => $query));

        $response = $this->getResponse($host, $query);

        $this->getEventManager()->trigger('debug.message', $this, array('message' => $response));

        if ($response['status'] == 'ok') {
            $this->getEventManager()->trigger(
                'onCodeRegister',
                $this,
                array(
                    $phone->getPhoneNumber(),
                    $response['login'],
                    $response['pw'],
                    $response['type'],
                    $response['expiration'],
                    $response['kind'],
                    $response['price'],
                    $response['cost'],
                    $response['currency'],
                    $response['price_expiration']
                )
            );
        } elseif ($response['status'] != 'sent') {
            if (isset($response['reason']) && $response['reason'] == "too_recent") {
                $this->getEventManager()->trigger(
                    'onCodeRequestFailedTooRecent',
                    $this,
                    array($phone->getPhoneNumber(), $method, $response['reason'], $response['retry_after'])
                );
                $minutes = round($response['retry_after'] / 60);
                throw new RuntimeException("Code already sent. Retry after $minutes minutes.");
            } else {
                $this->getEventManager()->trigger(
                    'onCodeRequestFailedTooRecent',
                    $this,
                    array($phone->getPhoneNumber(), $method, $response['reason'], $response['param'])
                );
                throw new RuntimeException('There was a problem trying to request the code.');
            }
        } else {
            $this->getEventManager()->trigger(
                'onCodeRequest',
                $this,
                array($phone->getPhoneNumber(), $method, $response['length'])
            );
        }

        return $response;
    }

    /**
     * Connect (create a socket) to the WhatsApp network.
     */
    public function connect()
    {
        $this->getEventManager()->trigger('connect.pre', $this);
        $socket = fsockopen(static::WHATSAPP_HOST, static::PORT);
        $params = compact('socket');
        if ($socket !== false) {
            stream_set_timeout($socket, static::TIMEOUT_SEC, static::TIMEOUT_USEC);
            $this->socket = $socket;
            $this->getEventManager()->trigger('connect.success', $this, $params);
        } else {
            $this->getEventManager()->trigger('connect.error', $this, $params);
        }
    }

    /**
     * Disconnect to the WhatsApp network.
     *
     * @return $this
     */
    public function disconnect()
    {
        if (null !== $this->socket) {
            fclose($this->socket);
            $this->getEventManager()->trigger('disconnect.success', $this);
        }
        return $this;
    }

    /**
     * Set the connection status with the WhatsApp server
     *
     * @param  boolean $connected
     * @return $this
     */
    public function setConnected($connected)
    {
        $this->connected = $connected;

        return $this;
    }

    /**
     * Get the connection status with the WhatsApp server
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @param  resource $socket
     * @return $this
     */
    protected function setSocket($socket)
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param  LocalizationService $localizationService
     * @return $this
     */
    public function setLocalizationService(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;

        return $this;
    }

    /**
     * @return LocalizationService
     */
    public function getLocalizationService()
    {
        if (!$this->localizationService) {
            $this->localizationService = new LocalizationService();
        }

        return $this->localizationService;
    }

    /**
     * @param  ProtocolService $protocolService
     * @return $this
     */
    public function setProtocolService($protocolService)
    {
        $this->protocolService = $protocolService;

        return $this;
    }

    /**
     * @return ProtocolService
     */
    public function getProtocolService()
    {
        if (!$this->protocolService) {
            $this->protocolService = new ProtocolService();
        }

        return $this->protocolService;
    }

    /**
     * Create an identity string
     *
     * @param  string $identity A user string
     * @return string Correctly formatted identity
     */
    protected function buildIdentity($identity)
    {
        return strtolower(urlencode(sha1($identity, true)));
    }

    /**
     * Check validity of an identity
     *
     * @param  string $identity
     * @return bool
     */
    protected function checkIdentity($identity)
    {
        return (strlen(urldecode($identity)) == 20);
    }

    /**
     * Get a decoded JSON response from Whatsapp server
     *
     * @param  string $host  The host URL
     * @param  array  $query A associative array of keys and values to send to server.
     * @return object NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit
     */
    protected function getResponse($host, array $query)
    {
        // Build the url.
        $url = $host . '?';
        if (function_exists('http_build_query')) {
            $url .= http_build_query($query);
        } else {
            foreach ($query as $key => $value) {
                $url .= $key . '=' . $value . '&';
            }
            $url = rtrim($url, '&');
        }

        // Open connection.
        $ch = curl_init();

        // Configure the connection.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, static::WHATSAPP_USER_AGENT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/json'));
        // This makes CURL accept any peer!
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Get the response.
        $response = curl_exec($ch);

        // Close the connection.
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Login to the Whatsapp server with your password
     *
     * If you already know your password you can log into the Whatsapp server
     * using this method.
     *
     * @param string $password         Your whatsapp password. You must already know this!
     * @param bool   $profileSubscribe Add a feature
     * @throws RuntimeException
     */
    public function loginWithPassword($password, $profileSubscribe = false)
    {
        $this->password = $password;
        $challengeData = $this->readChallengeData();
        if (!empty($challengeData)) {
            $this->challengeData = $challengeData;
        }
        $this->doLogin($profileSubscribe);
    }

    /**
     * Send the nodes to the Whatsapp server to log in.
     *
     * @param bool $profileSubscribe
     *                               Set this to true if you would like Whatsapp to send a
     *                               notification to your phone when one of your contacts
     *                               changes/update their picture.
     */
    protected function doLogin($profileSubscribe = false)
    {
        $this->nodeWriter->resetKey();
        $this->nodeReader->resetKey();
        $resource = static::WHATSAPP_DEVICE . '-' . static::WHATSAPP_VER . '-' . static::PORT;
        $data = $this->nodeWriter->startStream(static::WHATSAPP_SERVER, $resource);
        $feat = $this->createFeaturesNode($profileSubscribe);
        $auth = $this->createAuthNode();

        $this->sendData($data);
        $this->sendNode($feat);
        $this->sendNode($auth);

        $this->processInboundData($this->readData());

        if (!$this->isConnected()) {
            $data = $this->createAuthResponseNode();
            $this->sendNode($data);
            $this->nodeReader->setKey($this->inputKey);
            $this->nodeWriter->setKey($this->outputKey);
        }
        $cnt = 0;
        do {
            $this->processInboundData($this->readData());
        } while (($cnt++ < 100) && !$this->isConnected());

        $this->getEventManager()->trigger(
            'onLogin',
            $this,
            array($this->phone->getPhoneNumber())
        );
        $this->sendPresence();
    }

    /**
     * Send an action to the WhatsApp server.
     *
     * @param  Action\ActionInterface $action
     * @return Action\ActionInterface
     */
    public function send(Action\ActionInterface $action)
    {

        $this->getEventManager()->trigger('action.send.pre', $this, array('action' => $action));

        $node = $action->buildNode()->getNode();

        $eventParams = array('action' => $action, 'node' => $node, 'enqueued' => false);

        if ($action instanceof Action\MessageInterface) {
            // Am I still waiting the response of the last dequeued message?
            $this->getMessageQueue()->addMessage($action);
            if ($this->getMessageQueue()->hasParked()) {
                $eventParams['enqueued'] = true;
            } else {
                $this->sendNextMessage();
            }
        } else {
            // send without waiting
            $node = $this->sendNode($node);

            // updating the action with the id of the sent node
            $action->setNode($node);
            if ($node->hasAttribute('id')) {
                $action->setId($node->getAttribute('id'));
            }
        }

        $this->getEventManager()->trigger('action.send.post', $this, $eventParams);

        return $action;
    }

    public function sendNextMessage()
    {
        if ($this->getMessageQueue()->count()) {
            $action = $this->getMessageQueue()->getNextMessage();
            $action->setId(null);
            if ($action instanceof Action\MessageInterface) {
                $action->setTimestamp(time());
            }
            /** @var Action\ActionInterface $action */
            $node = $action->buildNode()->getNode();
            $node = $this->sendNode($node);

            // updating the action with the id of the sent node
            $action->setNode($node);
            if ($node->hasAttribute('id')) {
                $action->setId($node->getAttribute('id'));
            }
        }
        return $this;
    }

    /**
     * Send node to the WhatsApp server.
     * @param  NodeInterface $node
     * @return NodeInterface
     */
    public function sendNode(NodeInterface $node)
    {
        if ($node->hasAttribute('id') && (null === $node->getAttribute('id') || "" === $node->getAttribute('id'))) {
            $node->setAttribute('id', $node->getName() . '-' . time() . '-' . $this->getMessageCounter());
            $this->incrementMessageCounter();
        }

        $this->getEventManager()->trigger('node.send.pre', $this, array('node' => $node));

        $this->sendData($this->nodeWriter->write($node));

        $this->getEventManager()->trigger('node.send.post', $this, array('node' => $node));

        return $node;
    }

    /**
     * Send data to the whatsapp server.
     * @param  string $data
     * @return $this
     */
    protected function sendData($data)
    {
        if ($this->socket != null) {
            fwrite($this->socket, $data, strlen($data));
        }

        return $this;
    }

    /**
     * Read 1024 bytes from the whatsapp server.
     */
    protected function readData()
    {
        $buff = '';
        if ($this->socket != null) {
            $ret = @fread($this->socket, 1024);
            if ($ret) {
                $buff = $this->incompleteMessage . $ret;
                $this->incompleteMessage = '';
            } elseif (@feof($this->socket)) {
                $error = "socket EOF, closing socket...";
                fclose($this->socket);
                $this->socket = null;
                $this->getEventManager()->trigger('onClose', $this, array($this->phone->getPhoneNumber(), $error));
            }
        }

        return $buff;
    }

    /**
     * Pull from the socket, and place incoming messages in the message queue.
     *
     * @return $this
     */
    public function pollMessages()
    {
        $this->processInboundData($this->readData());

        return $this;
    }

    /**
     * Process inbound data.
     *
     * @param string $data
     *                     The data to process.
     */
    protected function processInboundData($data)
    {
        try {
            $node = $this->nodeReader->nextTree($data);
            while ($node != null) {

                $nodeEvent = new ReceivedNodeEvent();
                $nodeEvent->setClient($this);
                $nodeEvent->setTarget($this);
                $nodeEvent->setName('received.node.' . $node->getName());
                $nodeEvent->setNode($node);
                $this->getEventManager()->trigger($nodeEvent);

                $node = $this->nodeReader->nextTree();
            }
        } catch (IncompleteMessageException $e) {
            $this->incompleteMessage = $e->getInput();
        }
    }

    /**
     * Add stream features.
     * @param bool $profileSubscribe
     *
     * @return NodeInterface
     *                       Return itself.
     */
    protected function createFeaturesNode($profileSubscribe = false)
    {
        $children = array(
            array('name' => 'receipt_acks')
        );
        if ($profileSubscribe) {
            $children[] = array(
                'name' => 'w:profile:picture',
                'attributes' => array("type" => "all")
            );
        }
        $children[] = array(
            'name' => 'status',
        );
        $streamFeaturesNode = $this->getNodeFactory()->fromArray(
            array(
                'name' => 'stream:features',
                'children' => $children
            )
        );

        return $streamFeaturesNode;
    }

    /**
     * Add the authentication nodes.
     *
     * @return NodeInterface
     *                       Return itself.
     */
    protected function createAuthNode()
    {
        $authHash = array();
        $authHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $authHash["mechanism"] = "WAUTH-1";
        $authHash["user"] = $this->phone->getPhoneNumber();
        $data = $this->createAuthBlob();

        $node = $this->getNodeFactory()->fromArray(
            array(
                'name' => 'auth',
                'attributes' => $authHash,
                'data' => $data
            )
        );

        return $node;
    }

    protected function createAuthBlob()
    {
        if ($this->challengeData && false) {
            $key = $this->getProtocolService()->pbkdf2('sha1', base64_decode($this->password), $this->challengeData, 16, 20, true);
            $this->inputKey = new KeyStream(new RC4($key, 256), $key);
            $this->outputKey = new KeyStream(new RC4($key, 256), $key);
            $this->nodeReader->setKey($this->inputKey);
            //$this->nodeWriter->setKey($this->outputKey);
            $phone = $this->phone;
            $array = $phone->getPhoneNumber() .
                $this->challengeData .
                time() .
                static::WHATSAPP_USER_AGENT .
                " MccMnc/" .
                str_pad($phone->getMcc(), 3, "0", STR_PAD_LEFT) .
                "001";

            return $this->outputKey->encode($array, 0, strlen($array), false);
        }

        return null;
    }

    /**
     * Add the auth response
     *
     * @return NodeInterface
     */
    protected function createAuthResponseNode()
    {
        $resp = $this->authenticate();
        $respHash = array();
        $respHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";

        $node = $this->getNodeFactory()->fromArray(
            array(
                'name' => 'response',
                'attributes' => $respHash,
                'data' => $resp
            )
        );

        return $node;
    }

    /**
     * Create a unique msg id.
     *
     * @param  string $prefix
     * @return string A message id string.
     */
    protected function createMsgId($prefix)
    {
        $msgid = "$prefix-" . time() . '-' . $this->messageCounter;
        $this->messageCounter++;

        return $msgid;
    }

    /**
     * Authenticate with the Whatsapp Server.
     *
     * @return string
     *                Returns binary string
     */
    protected function authenticate()
    {
        $key = $this->getProtocolService()
            ->pbkdf2('sha1', base64_decode($this->password), $this->challengeData, 16, 20, true);
        $this->inputKey = new KeyStream(new RC4($key, 256), $key);
        $this->outputKey = new KeyStream(new RC4($key, 256), $key);
        $array = $this->phone->getPhoneNumber() . $this->challengeData . time();
        $response = $this->outputKey->encode($array, 0, strlen($array), false);

        return $response;
    }

    /**
     * Send presence status.
     *
     * @param string $type
     *                     The presence status.
     */
    public function sendPresence($type = "available")
    {
        $presence = array();
        $presence['type'] = $type;
        $presence['name'] = $this->nickname;

        $node = $this->getNodeFactory()->fromArray(
            array(
                'name' => 'presence',
                'attributes' => $presence,
            )
        );

        $this->sendNode($node);
        $this->getEventManager()->trigger(
            'onSendPresence',
            $this,
            array($this->phone->getPhoneNumber(), $presence['type'], @$presence['name'])
        );
    }

    /**
     * @param  \Tmv\WhatsApi\Protocol\BinTree\NodeWriter $nodeWriter
     * @return $this
     */
    public function setNodeWriter($nodeWriter)
    {
        $this->nodeWriter = $nodeWriter;

        return $this;
    }

    /**
     * @return \Tmv\WhatsApi\Protocol\BinTree\NodeWriter
     */
    public function getNodeWriter()
    {
        return $this->nodeWriter;
    }

    /**
     * @param  \Tmv\WhatsApi\Protocol\BinTree\NodeReader $nodeReader
     * @return $this
     */
    public function setNodeReader($nodeReader)
    {
        $this->nodeReader = $nodeReader;

        return $this;
    }

    /**
     * @return \Tmv\WhatsApi\Protocol\BinTree\NodeReader
     */
    public function getNodeReader()
    {
        return $this->nodeReader;
    }

    /**
     * @param  \Tmv\WhatsApi\Protocol\KeyStream $inputKey
     * @return $this
     */
    public function setInputKey($inputKey)
    {
        $this->inputKey = $inputKey;

        return $this;
    }

    /**
     * @return \Tmv\WhatsApi\Protocol\KeyStream
     */
    public function getInputKey()
    {
        return $this->inputKey;
    }

    /**
     * @param  \Tmv\WhatsApi\Protocol\KeyStream $outputKey
     * @return $this
     */
    public function setOutputKey($outputKey)
    {
        $this->outputKey = $outputKey;

        return $this;
    }

    /**
     * @return \Tmv\WhatsApi\Protocol\KeyStream
     */
    public function getOutputKey()
    {
        return $this->outputKey;
    }

    /**
     * @param  mixed $challengeData
     * @return $this
     */
    public function setChallengeData($challengeData)
    {
        $this->challengeData = $challengeData;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChallengeData()
    {
        return $this->challengeData;
    }

    /**
     * @param  string $filePath
     * @return $this
     */
    public function setChallengeDataFilepath($filePath)
    {
        $this->challengeDataFilepath = $filePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getChallengeDataFilepath()
    {
        return $this->challengeDataFilepath;
    }

    /**
     * @param $data
     * @return $this
     */
    public function writeChallengeData($data)
    {
        $this->checkChallengeDataFilePermission();
        $filepath = $this->getChallengeDataFilepath();
        file_put_contents($filepath, $data);
        return $this;
    }

    /**
     * @return string
     */
    public function readChallengeData()
    {
        $this->checkChallengeDataFilePermission();
        $filepath = $this->getChallengeDataFilepath();
        return file_get_contents($filepath);
    }

    /**
     * @return bool
     * @throws \Tmv\WhatsApi\Exception\RuntimeException
     */
    public function checkChallengeDataFilePermission()
    {
        $filePath = $this->getChallengeDataFilepath();
        if (!$filePath) {
            throw new RuntimeException("Filename for challenge data is not setted");
        }
        $baseDir = dirname($filePath);
        if (!file_exists($baseDir)) {
            throw new RuntimeException(sprintf("Directory '%s' doesn't exists", $baseDir));
        } elseif (!file_exists($filePath) && !is_writable($baseDir)) {
            throw new RuntimeException(sprintf("Directory '%s' is not writable", $baseDir));
        } elseif (!file_exists($filePath)) {
            touch($filePath);
        }

        if (!is_writable($filePath)) {
            throw new RuntimeException(sprintf("File '%s' is not writable", $filePath));
        }
        return true;
    }

    /**
     * @return $this
     */
    public function incrementMessageCounter()
    {
        $this->messageCounter++;

        return $this;
    }

    /**
     * @param  int   $messageCounter
     * @return $this
     */
    public function setMessageCounter($messageCounter)
    {
        $this->messageCounter = $messageCounter;

        return $this;
    }

    /**
     * @return int
     */
    public function getMessageCounter()
    {
        return $this->messageCounter;
    }

    /**
     * @return MessageQueue
     */
    public function getMessageQueue()
    {
        if (!$this->messageQueue) {
            $this->messageQueue = new MessageQueue();
        }
        return $this->messageQueue;
    }

    /**
     * @return \Tmv\WhatsApi\Entity\Phone
     */
    public function getPhone()
    {
        return $this->phone;
    }
}
