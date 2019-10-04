<?php

namespace Tmv\WhatsApi\Protocol;

class Dictionary implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $container = array(
        0   => 0,
        1   => 0,
        2   => 0,
        3   => 0,
        4   => 0,
        5   => "account",
        6   => "ack",
        7   => "action",
        8   => "active",
        9   => "add",
        10  => "after",
        11  => "ib",
        12  => "all",
        13  => "allow",
        14  => "apple",
        15  => "audio",
        16  => "auth",
        17  => "author",
        18  => "available",
        19  => "bad-protocol",
        20  => "bad-request",
        21  => "before",
        22  => "Bell.caf",
        23  => "body",
        24  => "Boing.caf",
        25  => "cancel",
        26  => "category",
        27  => "challenge",
        28  => "chat",
        29  => "clean",
        30  => "code",
        31  => "composing",
        32  => "config",
        33  => "conflict",
        34  => "contacts",
        35  => "count",
        36  => "create",
        37  => "creation",
        38  => "default",
        39  => "delay",
        40  => "delete",
        41  => "delivered",
        42  => "deny",
        43  => "digest",
        44  => "DIGEST-MD5-1",
        45  => "DIGEST-MD5-2",
        46  => "dirty",
        47  => "elapsed",
        48  => "broadcast",
        49  => "enable",
        50  => "encoding",
        51  => "duplicate",
        52  => "error",
        53  => "event",
        54  => "expiration",
        55  => "expired",
        56  => "fail",
        57  => "failure",
        58  => "false",
        59  => "favorites",
        60  => "feature",
        61  => "features",
        62  => "field",
        63  => "first",
        64  => "free",
        65  => "from",
        66  => "g.us",
        67  => "get",
        68  => "Glass.caf",
        69  => "google",
        70  => "group",
        71  => "groups",
        72  => "g_notify",
        73  => "g_sound",
        74  => "Harp.caf",
        75  => "http://etherx.jabber.org/streams",
        76  => "http://jabber.org/protocol/chatstates",
        77  => "id",
        78  => "image",
        79  => "img",
        80  => "inactive",
        81  => "index",
        82  => "internal-server-error",
        83  => "invalid-mechanism",
        84  => "ip",
        85  => "iq",
        86  => "item",
        87  => "item-not-found",
        88  => "user-not-found",
        89  => "jabber:iq:last",
        90  => "jabber:iq:privacy",
        91  => "jabber:x:delay",
        92  => "jabber:x:event",
        93  => "jid",
        94  => "jid-malformed",
        95  => "kind",
        96  => "last",
        97  => "latitude",
        98  => "lc",
        99  => "leave",
        100 => "leave-all",
        101 => "lg",
        102 => "list",
        103 => "location",
        104 => "longitude",
        105 => "max",
        106 => "max_groups",
        107 => "max_participants",
        108 => "max_subject",
        109 => "mechanism",
        110 => "media",
        111 => "message",
        112 => "message_acks",
        113 => "method",
        114 => "microsoft",
        115 => "missing",
        116 => "modify",
        117 => "mute",
        118 => "name",
        119 => "nokia",
        120 => "none",
        121 => "not-acceptable",
        122 => "not-allowed",
        123 => "not-authorized",
        124 => "notification",
        125 => "notify",
        126 => "off",
        127 => "offline",
        128 => "order",
        129 => "owner",
        130 => "owning",
        131 => "paid",
        132 => "participant",
        133 => "participants",
        134 => "participating",
        135 => "password",
        136 => "paused",
        137 => "picture",
        138 => "pin",
        139 => "ping",
        140 => "platform",
        141 => "pop_mean_time",
        142 => "pop_plus_minus",
        143 => "port",
        144 => "presence",
        145 => "preview",
        146 => "probe",
        147 => "proceed",
        148 => "prop",
        149 => "props",
        150 => "p_o",
        151 => "p_t",
        152 => "query",
        153 => "raw",
        154 => "reason",
        155 => "receipt",
        156 => "receipt_acks",
        157 => "received",
        158 => "registration",
        159 => "relay",
        160 => "remote-server-timeout",
        161 => "remove",
        162 => "Replaced by new connection",
        163 => "request",
        164 => "required",
        165 => "resource",
        166 => "resource-constraint",
        167 => "response",
        168 => "result",
        169 => "retry",
        170 => "rim",
        171 => "s.whatsapp.net",
        172 => "s.us",
        173 => "seconds",
        174 => "server",
        175 => "server-error",
        176 => "service-unavailable",
        177 => "set",
        178 => "show",
        179 => "sid",
        180 => "silent",
        181 => "sound",
        182 => "stamp",
        183 => "unsubscribe",
        184 => "stat",
        185 => "status",
        186 => "stream:error",
        187 => "stream:features",
        188 => "subject",
        189 => "subscribe",
        190 => "success",
        191 => "sync",
        192 => "system-shutdown",
        193 => "s_o",
        194 => "s_t",
        195 => "t",
        196 => "text",
        197 => "timeout",
        198 => "TimePassing.caf",
        199 => "timestamp",
        200 => "to",
        201 => "Tri-tone.caf",
        202 => "true",
        203 => "type",
        204 => "unavailable",
        205 => "uri",
        206 => "url",
        207 => "urn:ietf:params:xml:ns:xmpp-sasl",
        208 => "urn:ietf:params:xml:ns:xmpp-stanzas",
        209 => "urn:ietf:params:xml:ns:xmpp-streams",
        210 => "urn:xmpp:delay",
        211 => "urn:xmpp:ping",
        212 => "urn:xmpp:receipts",
        213 => "urn:xmpp:whatsapp",
        214 => "urn:xmpp:whatsapp:account",
        215 => "urn:xmpp:whatsapp:dirty",
        216 => "urn:xmpp:whatsapp:mms",
        217 => "urn:xmpp:whatsapp:push",
        218 => "user",
        219 => "username",
        220 => "value",
        221 => "vcard",
        222 => "version",
        223 => "video",
        224 => "w",
        225 => "w:g",
        226 => "w:p",
        227 => "w:p:r",
        228 => "w:profile:picture",
        229 => "wait",
        230 => "x",
        231 => "xml-not-well-formed",
        232 => "xmlns",
        233 => "xmlns:stream",
        234 => "Xylophone.caf",
        235 => "1",
        236 => "WAUTH-1",
        237 => 0,
        238 => 0,
        239 => 0,
        240 => 0,
        241 => 0,
        242 => 0,
        243 => 0,
        244 => 0,
        245 => 0,
        246 => 0,
        247 => 0,
        248 => "XXX"
    );

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->container);
    }

    public function count()
    {
        return count($this->container);
    }
}
