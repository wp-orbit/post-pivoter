<?php
namespace WPOrbit\MetaBoxes;

/**
 * Registers a meta box to an array of post types.
 *
 * Class MetaBox
 * @package WPOrbit\MetaBoxes
 */
class MetaBox
{
    /**
     * @var string The meta box ID.
     */
    protected $id;

    /**
     * @var string The meta box label.
     */
    protected $label;

    /**
     * @var array An array of post types this meta box should be hooked into.
     */
    protected $postTypes = [];

    /**
     * @var array An array of post slugs that this should be hooked into.
     */
    protected $postSlugs = [];

    /**
     * Get meta box (element) ID.
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get meta box label.
     * @return mixed|string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function __construct( $args = [] )
    {
        if ( isset( $args['id'] ) ) {
            $this->id = $args['id'];
        }
        if ( isset( $args['label'] ) ) {
            $this->label = $args['label'];
        }
        if ( isset( $args['postTypes'] ) ) {
            $this->postTypes = $args['postTypes'];
        }
        if ( isset( $args['postSlugs'] ) ) {
            $this->postSlugs = $args['postSlugs'];
        }

        $this->register();
    }

    /**
     * @param \WP_Post $post
     */
    public function render( \WP_Post $post )
    {
        echo 'Override the render function.';
    }

    public function save($postId, $postType)
    {
        // Override save function..
    }

    public function register()
    {
        foreach( $this->postTypes as $postType )
        {
            add_action( "add_meta_boxes_{$postType}", function($post)
            {
                // Check if there are matching post slugs.
                if ( ! empty( $this->postSlugs ) && ! in_array( $post->post_name, $this->postSlugs ) ) {
                    return;
                }
                add_meta_box(
                    $this->id,
                    $this->label,
                    [$this, 'render'],
                    null,
                    'normal',
                    'default'
                );
            });

            add_action( 'save_post', function($postId) use ($postType)
            {
                $this->save($postId, $postType);
            });
        }
    }
}