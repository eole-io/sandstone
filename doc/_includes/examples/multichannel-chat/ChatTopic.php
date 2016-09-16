class ChatTopic extends Eole\Sandstone\Websocket\Topic
{
    /**
     * Broadcast message to each subscribing client.
     *
     * {@InheritDoc}
     */
    public function onPublish(Ratchet\Wamp\WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'message' => $event,
        ]);
    }

    /**
     * Notify all subscribing clients that a new client has subscribed to this channel.
     *
     * {@InheritDoc}
     */
    public function onSubscribe(Ratchet\Wamp\WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        $this->broadcast([
            'type' => 'join',
            'message' => 'Someone has joined this channel.',
        ]);
    }

    /**
     * Notify all subscribing clients that a new client has unsubscribed from this channel.
     *
     * {@InheritDoc}
     */
    public function onUnSubscribe(Ratchet\Wamp\WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);

        $this->broadcast([
            'type' => 'leave',
            'message' => 'Someone has left this channel.',
        ]);
    }
}
