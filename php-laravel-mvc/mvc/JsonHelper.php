<?php
/**
 * Helper class for manipulating JSON input and responses 
 */

class JsonHelper {
    
    /**
     * Returns json representation of the error response based on
     * the specified parameters.
     * 
     * @param int $code  Status code  
     * @param string $message  Messege used in the reponse related to the error    
     * @return json  JSON representation of the error response
     */
    public static function createErrorResponse($code, $message) {
        return json_encode(array(
                'status' => $code,
                'message' => $message
        ));
    }
    
    
    /**
     * Returns json representation of the resources
     * together with confirming message. There is no payload.
     *
     * @return json
     */
    public static function createEmptyResponse() {
        return json_encode(array(
                'status' => 0,
                'message' => 'OK'
        ));
    }


    /**
     * Returns json representation of the resources
     * together with confirming message/status and payload
     *
     * @param mixed $message  Payload included in the response
     * @return json  JSON representation of the payload response
     */
    public static function createPayloadResponse($payload) {
        return json_encode(array(
                'status' => 0,
                'message' => 'OK',
                'payload' => $payload
        ));
    }

} 