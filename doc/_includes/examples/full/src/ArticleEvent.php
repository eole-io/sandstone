use Symfony\Component\EventDispatcher\Event;

class ArticleEvent extends Event
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $url;
}
