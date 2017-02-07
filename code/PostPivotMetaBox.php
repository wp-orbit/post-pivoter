<?php
namespace WPOrbit\MetaBoxes;
use WPOrbit\Taxonomies\TaxonomyPivoter;

/**
 * A post pivot metabox "pivots" relationships between post types through a
 * taxonomy whose term names are the related post type.
 *
 * Class PostPivotMetaBox
 *
 * @package WPOrbit\MetaBoxes
 */
class PostPivotMetaBox extends MetaBox
{
    protected $taxonomy;

    public function __construct( $args = [] )
    {
        parent::__construct( $args );

        if ( isset( $args['taxonomy'] ) ) {
            $this->taxonomy = $args['taxonomy'];
        }
    }

    public function render( \WP_Post $post )
    {
        // A taxonomy is required.
        if ( ! $this->taxonomy || empty( $this->taxonomy ) ) {
            echo '<p>No taxonomy was specified for pivoting posts.</p>';
            return;
        }

        // Get post type.
        $postType = $post->post_type;

        // Taxonomy.
        $taxonomy = $this->taxonomy;

        // Make pivoter.
        $pivoter = new TaxonomyPivoter( $post->ID, $taxonomy );
        $pivoter->getIds();

        // Todo-- implement a dynamic form that works with related IDs.
    }

    public function save( $postId, $postType )
    {
        parent::save( $postId, $postType );
    }
}