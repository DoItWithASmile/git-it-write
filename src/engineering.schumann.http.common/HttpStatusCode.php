<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\http\common;


if( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTTP constants
 * 
 * Content from http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
 */
final class HTTP_STATUS_CODE {
    /* ====================
     * official
     * ==================== */

    /* == 1xx informational response ==
     * An informational response indicates that the request was received and understood. It is issued on a provisional basis while request processing continues. It alerts the client to wait for a final response. The message consists only of the status line and optional header fields, and is terminated by an empty line. As the HTTP/1.0 standard did not define any 1xx status codes, servers must not[note 1] send a 1xx response to an HTTP/1.0 compliant client except under experimental conditions.
     */

    /**
     * The server has received the request headers and the client should proceed to send the request body (in the case of a request for which a body needs to be sent; for example, a POST request). Sending a large request body to a server after a request has been rejected for inappropriate headers would be inefficient. To have a server check the request's headers, a client must send Expect: 100-continue as a header in its initial request and receive a 100 Continue status code in response before sending the body. If the client receives an error code such as 403 (Forbidden) or 405 (Method Not Allowed) then it should not send the request's body. The response 417 Expectation Failed indicates that the request should be repeated without the Expect header as it indicates that the server does not support expectations (this is the case, for example, of HTTP/1.0 servers).[2]
     */
    const STATUS_100_Continue = 100;

    /**
     * The requester has asked the server to switch protocols and the server has agreed to do so.
     */
    const STATUS_101_Switching_Protocols = 101;

    /**
     * A WebDAV request may contain many sub-requests involving file operations, requiring a long time to complete the request. This code indicates that the server has received and is processing the request, but no response is available yet.[3] This prevents the client from timing out and assuming the request was lost.
     * 
     * @see WebDAV; RFC 2518
     */
    const STATUS_102_Processing = 102;

    /**
     * Used to return some response headers before final HTTP message.
     * 
     * @see RFC 8297
     */
    const STATUS_103_Early_Hints = 103;

    /* == 2xx success ==
     * This class of status codes indicates the action requested by the client was received, understood, and accepted.[1]
     */

    /**
     * Standard response for successful HTTP requests. The actual response will depend on the request method used. In a GET request, the response will contain an entity corresponding to the requested resource. In a POST request, the response will contain an entity describing or containing the result of the action.
     */
    const STATUS_200_OK = 200;

    /**
     * The request has been fulfilled, resulting in the creation of a new resource.
     */
    const STATUS_201_Created = 201;

    /**
     * The request has been accepted for processing, but the processing has not been completed. The request might or might not be eventually acted upon, and may be disallowed when processing occurs.
     */
    const STATUS_202_Accepted = 202;

    /**
     * The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is returning a modified version of the origin's response.
     * 
     * since HTTP/1.1
     */
    const STATUS_203_Non_Authoritative_Information = 203;

    /**
     * The server successfully processed the request, and is not returning any content.
     */
    const STATUS_204_No_Content = 204;

    /**
     * The server successfully processed the request, asks that the requester reset its document view, and is not returning any content.
     */
    const STATUS_205_Reset_Content = 205;

    /**
     * The server is delivering only part of the resource (byte serving) due to a range header sent by the client. The range header is used by HTTP clients to enable resuming of interrupted downloads, or split a download into multiple simultaneous streams.
     */
    const STATUS_206_Partial_Content = 206;

    /**
     * The message body that follows is by default an XML message and can contain a number of separate response codes, depending on how many sub-requests were made.
     * 
     * @see WebDAV; RFC 4918
     */
    const STATUS_207_Multi_Status = 207;

    /**
     * The members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response, and are not being included again.
     * 
     * @see WebDAV; RFC 5842
     */
    const STATUS_208_Already_Reported = 208;

    /**
     * The server has fulfilled a request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.
     * 
     * @see RFC 3229
     */
    const STATUS_226_IM_Used = 226;


    /* == 3xx redirection ==
     * This class of status code indicates the client must take additional action to complete the request. Many of these status codes are used in URL redirection.
     *
     * A user agent may carry out the additional action with no user interaction only if the method used in the second request is GET or HEAD. A user agent may automatically redirect a request. A user agent should detect and intervene to prevent cyclical redirects.
     */

    /**
     * Indicates multiple options for the resource from which the client may choose (via agent-driven content negotiation). For example, this code could be used to present multiple video format options, to list files with different filename extensions, or to suggest word-sense disambiguation.
     */
    const STATUS_300_Multiple_Choices = 300;

    /**
     * This and all future requests should be directed to the given URI.
     */
    const STATUS_301_Moved_Permanently = 301;

    /**
     * Tells the client to look at (browse to) another URL. The HTTP/1.0 specification (RFC 1945) required the client to perform a temporary redirect with the same method (the original describing phrase was "Moved Temporarily"), but popular browsers implemented 302 redirects by changing the method to GET. Therefore, HTTP/1.1 added status codes 303 and 307 to distinguish between the two behaviours.
     * 
     * Previously "Moved temporarily"
     */
    const STATUS_302_Found = 302;

    /**
     * The response to the request can be found under another URI using the GET method. When received in response to a POST (or PUT/DELETE), the client should presume that the server has received the data and should issue a new GET request to the given URI.
     * 
     * since HTTP/1.1
     */
    const STATUS_303_See_Other = 303;

    /**
     * Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match. In such case, there is no need to retransmit the resource since the client still has a previously-downloaded copy.
     */
    const STATUS_304_Not_Modified = 304;

    /**
     * The requested resource is available only through a proxy, the address for which is provided in the response. For security reasons, many HTTP clients (such as Mozilla Firefox and Internet Explorer) do not obey this status code.
     * 
     * since HTTP/1.1
     */
    const STATUS_305_Use_Proxy = 305;

    /**
     * No longer used. Originally meant "Subsequent requests should use the specified proxy."
     */
    const STATUS_306_Switch_Proxy = 306;

    /**
     * In this case, the request should be repeated with another URI; however, future requests should still use the original URI. In contrast to how 302 was historically implemented, the request method is not allowed to be changed when reissuing the original request. For example, a POST request should be repeated using another POST request.
     * 
     * since HTTP/1.1
     */
    const STATUS_307_Temporary_Redirect = 307;

    /**
     * This and all future requests should be directed to the given URI. 308 parallel the behaviour of 301, but does not allow the HTTP method to change. So, for example, submitting a form to a permanently redirected resource may continue smoothly.
     */
    const STATUS_308_Permanent_Redirect = 308;

    
    /* == 4xx client errors ==
     * This class of status code is intended for situations in which the error seems to have been caused by the client. Except when responding to a HEAD request, the server should include an entity containing an explanation of the error situation, and whether it is a temporary or permanent condition. These status codes are applicable to any request method. User agents should display any included entity to the user.
     */

    /**
     * The server cannot or will not process the request due to an apparent client error (e.g., malformed request syntax, size too large, invalid request message framing, or deceptive request routing).
     */
    const STATUS_400_Bad_Request = 400;

    /**
     * Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to the requested resource. See Basic access authentication and Digest access authentication. 401 semantically means "unauthorised", the user does not have valid authentication credentials for the target resource.
     * Note: Some sites incorrectly issue HTTP 401 when an IP address is banned from the website (usually the website domain) and that specific address is refused permission to access a website.
     */
    const STATUS_401_Unauthorized = 401;

    /**
     * Reserved for future use. The original intention was that this code might be used as part of some form of digital cash or micropayment scheme, as proposed, for example, by GNU Taler,[13] but that has not yet happened, and this code is not widely used. Google Developers API uses this status if a particular developer has exceeded the daily limit on requests.[14] Sipgate uses this code if an account does not have sufficient funds to start a call.[15] Shopify uses this code when the store has not paid their fees and is temporarily disabled.[16] Stripe uses this code for failed payments where parameters were correct, for example blocked fraudulent payments.[17]
     */
    const STATUS_402_Payment_Required = 402;

    /**
     * The request contained valid data and was understood by the server, but the server is refusing action. This may be due to the user not having the necessary permissions for a resource or needing an account of some sort, or attempting a prohibited action (e.g. creating a duplicate record where only one is allowed). This code is also typically used if the request provided authentication by answering the WWW-Authenticate header field challenge, but the server did not accept that authentication. The request should not be repeated.
     */
    const STATUS_403_Forbidden = 403;

    /**
     * The requested resource could not be found but may be available in the future. Subsequent requests by the client are permissible.
     */
    const STATUS_404_Not_Found = 404;

    /**
     * A request method is not supported for the requested resource; for example, a GET request on a form that requires data to be presented via POST, or a PUT request on a read-only resource.
     */
    const STATUS_405_Method_Not_Allowed = 405;

    /**
     * The requested resource is capable of generating only content not acceptable according to the Accept headers sent in the request. See Content negotiation.
     */
    const STATUS_406_Not_Acceptable = 406;

    /**
     * The client must first authenticate itself with the proxy.
     */
    const STATUS_407_Proxy_Authentication_Required = 407;

    /**
     * The server timed out waiting for the request. According to HTTP specifications: "The client did not produce a request within the time that the server was prepared to wait. The client MAY repeat the request without modifications at any later time."
     */
    const STATUS_408_Request_Timeout = 408;

    /**
     * Indicates that the request could not be processed because of conflict in the current state of the resource, such as an edit conflict between multiple simultaneous updates.
     */
    const STATUS_409_Conflict = 409;

    /**
     * Indicates that the resource requested was previously in use but is no longer available and will not be available again. This should be used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410 status code, the client should not request the resource in the future. Clients such as search engines should remove the resource from their indices. Most use cases do not require clients and search engines to purge the resource, and a "404 Not Found" may be used instead.
     */
    const STATUS_410_Gone = 410;

    /**
     * The request did not specify the length of its content, which is required by the requested resource.
     */
    const STATUS_411_Length_Required = 411;

    /**
     * The server does not meet one of the preconditions that the requester put on the request header fields.
     */
    const STATUS_412_Precondition_Failed = 412;

    /**
     * The request is larger than the server is willing or able to process. Previously called "Request Entity Too Large" in RFC 2616.
     */
    const STATUS_413_Payload_Too_Large = 413;

    /**
     * The URI provided was too long for the server to process. Often the result of too much data being encoded as a query-string of a GET request, in which case it should be converted to a POST request. Called "Request-URI Too Long" previously in RFC 2616.
     */
    const STATUS_414_URI_Too_Long = 414;

    /**
     * The request entity has a media type which the server or resource does not support. For example, the client uploads an image as image/svg+xml, but the server requires that images use a different format.
     */
    const STATUS_415_Unsupported_Media_Type = 415;

    /**
     * The client has asked for a portion of the file (byte serving), but the server cannot supply that portion. For example, if the client asked for a part of the file that lies beyond the end of the file. Called "Requested Range Not Satisfiable" previously RFC 2616.
     */
    const STATUS_416_Range_Not_Satisfiable = 416;

    /**
     * The server cannot meet the requirements of the Expect request-header field.
     */
    const STATUS_417_Expectation_Failed = 417;

    /**
     * This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324, Hyper Text Coffee Pot Control Protocol, and is not expected to be implemented by actual HTTP servers. The RFC specifies this code should be returned by teapots requested to brew coffee. This HTTP status is used as an Easter egg in some websites, such as Google.com's "I'm a teapot" easter egg. Sometimes, this status code is also used as a response to a blocked request, instead of the more appropriate 403 Forbidden.
     * 
     * @see RFC 2324, RFC 7168
     */
    const STATUS_418_I_am_a_teapot = 418;

    /**
     * The request was directed at a server that is not able to produce a response (for example because of connection reuse).
     */
    const STATUS_421_Misdirected_Request = 421;

    /**
     * The request was well-formed but was unable to be followed due to semantic errors.
     * 
     * @see WebDAV; RFC 4918
     */
    const STATUS_422_Unprocessable_Entity = 422;

    /**
     * The resource that is being accessed is locked.
     * 
     * @see WebDAV; RFC 4918
     */
    const STATUS_423_Locked = 423;

    /**
     * The request failed because it depended on another request and that request failed (e.g., a PROPPATCH).
     * 
     * @see WebDAV; RFC 4918
     */
    const STATUS_424_Failed_Dependency = 424;

    /**
     * Indicates that the server is unwilling to risk processing a request that might be replayed.
     * 
     * @see RFC 8470
     */
    const STATUS_425_Too_Early = 425;

    /**
     * The client should switch to a different protocol such as TLS/1.3, given in the Upgrade header field.
     */
    const STATUS_426_Upgrade_Required = 426;

    /**
     * The origin server requires the request to be conditional. Intended to prevent the 'lost update' problem, where a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict.
     * 
     * @see RFC 6585
     */
    const STATUS_428_Precondition_Required = 428;

    /**
     * The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.
     * 
     * @see RFC 6585
     */
    const STATUS_429_Too_Many_Requests = 429;

    /**
     * The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.
     * 
     * @see RFC 6585
     */
    const STATUS_431_Request_Header_Fields_Too_Large = 431;

    /**
     * A server operator has received a legal demand to deny access to a resource or to a set of resources that includes the requested resource. The code 451 was chosen as a reference to the novel Fahrenheit 451 (see the Acknowledgements in the RFC).
     * 
     * @see RFC 7725
     */
    const STATUS_451_Unavailable_For_Legal_Reasons = 451;


    /* == 5xx server errors ==
     * The server failed to fulfil a request.
     * Response status codes beginning with the digit "5" indicate cases in which the server is aware that it has encountered an error or is otherwise incapable of performing the request. Except when responding to a HEAD request, the server should include an entity containing an explanation of the error situation, and indicate whether it is a temporary or permanent condition. Likewise, user agents should display any included entity to the user. These response codes are applicable to any request method.
     */

    /**
     * A generic error message, given when an unexpected condition was encountered and no more specific message is suitable.
     */
    const STATUS_500_Internal_Server_Error = 500;

    /**
     * The server either does not recognize the request method, or it lacks the ability to fulfil the request. Usually this implies future availability (e.g., a new feature of a web-service API).
     */
    const STATUS_501_Not_Implemented = 501;

    /**
     * The server was acting as a gateway or proxy and received an invalid response from the upstream server.
     */
    const STATUS_502_Bad_Gateway = 502;

    /**
     * The server cannot handle the request (because it is overloaded or down for maintenance). Generally, this is a temporary state.
     */
    const STATUS_503_Service_Unavailable = 503;

    /**
     * The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
     */
    const STATUS_504_Gateway_Timeout = 504;

    /**
     * The server does not support the HTTP version used in the request.
     */
    const STATUS_505_HTTP_Version_Not_Supported = 505;

    /**
     * Transparent content negotiation for the request results in a circular reference.
     * 
     * @see RFC 2295
     */
    const STATUS_506_Variant_Also_Negotiates = 506;

    /**
     * The server is unable to store the representation needed to complete the request.
     * 
     * @see WebDAV; RFC 4918
     */
    const STATUS_507_Insufficient_Storage = 507;

    /**
     * The server detected an infinite loop while processing the request (sent instead of 208 Already Reported).
     * 
     * @see WebDAV; RFC 5842
     */
    const STATUS_508_Loop_Detected = 508;

    /**
     * Further extensions to the request are required for the server to fulfill it.
     * 
     * @see RFC 2774
     */
    const STATUS_510_Not_Extended = 510;

    /**
     * The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to control access to the network (e.g., "captive portals" used to require agreement to Terms of Service before granting full Internet access via a Wi-Fi hotspot).
     * 
     * @see RFC 6585
     */
    const STATUS_511_Network_Authentication_Required = 511;


    /* ====================
     * unofficial
     * ==================== */
    const STATUS_UNOFFICIAL_103_Checkpoint                             = 103;

    const STATUS_UNOFFICIAL_218_This_is_fine                           = 218; // Apache Web Server

    const STATUS_UNOFFICIAL_419_Page_Expired                           = 419; // Laravel Framework
    const STATUS_UNOFFICIAL_420_Method_Failure                         = 420; // Spring Framework
    const STATUS_UNOFFICIAL_420_Enhance_Your_Calm                      = 420; // Twitter
    const STATUS_UNOFFICIAL_430_Request_Header_Fields_Too_Large        = 430; // Shopify
    const STATUS_UNOFFICIAL_498_Invalid_Token                          = 498; // Esri
    const STATUS_UNOFFICIAL_499_Token_Required                         = 499; // Esri
    const STATUS_UNOFFICIAL_450_Blocked_by_Windows_Parental_Controls   = 450; // Microsoft
    
    const STATUS_UNOFFICIAL_440_Login_Time_out                         = 440; // IIS
    const STATUS_UNOFFICIAL_444_No_Response                            = 444; // nginx
    const STATUS_UNOFFICIAL_449_Retry_With                             = 449; // IIS
    const STATUS_UNOFFICIAL_451_Redirect                               = 451; // IIS
    const STATUS_UNOFFICIAL_494_Request_header_too_large               = 494; // nginx
    const STATUS_UNOFFICIAL_495_SSL_Certificate_Error                  = 495; // nginx
    const STATUS_UNOFFICIAL_496_SSL_Certificate_Required               = 496; // nginx
    const STATUS_UNOFFICIAL_497_HTTP_Request_Sent_to_HTTPS_Port        = 497; // nginx
    const STATUS_UNOFFICIAL_499_Client_Closed_Request                  = 499; // nginx

    const STATUS_UNOFFICIAL_509_Bandwidth_Limit_Exceeded               = 509; // Apache Web Server/cPanel
    const STATUS_UNOFFICIAL_520_Web_Server_Returned_an_Unknown_Error   = 520; // Cloudflare
    const STATUS_UNOFFICIAL_521_Web_Server_Is_Down                     = 521; // Cloudflare
    const STATUS_UNOFFICIAL_522_Connection_Timed_Out                   = 522; // Cloudflare
    const STATUS_UNOFFICIAL_523_Origin_Is_Unreachable                  = 523; // Cloudflare
    const STATUS_UNOFFICIAL_524_A_Timeout_Occurred                     = 524; // Cloudflare
    const STATUS_UNOFFICIAL_525_SSL_Handshake_Failed                   = 525; // Cloudflare
    const STATUS_UNOFFICIAL_526_Invalid_SSL_Certificate                = 526; // Cloudflare
    const STATUS_UNOFFICIAL_527_Railgun_Error                          = 527; // Cloudflare
    const STATUS_UNOFFICIAL_529_Site_is_overloaded                     = 529; // Qualys in the SSLLabs
    const STATUS_UNOFFICIAL_530_Site_is_frozen                         = 530; // Pantheon web platform
    const STATUS_UNOFFICIAL_598_Network_read_timeout_error             = 598; // Informal convention
}
