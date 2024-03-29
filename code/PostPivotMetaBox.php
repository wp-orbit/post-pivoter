<?php
namespace WPOrbit\MetaBoxes;

use WPOrbit\Taxonomies\Pivoter\TaxonomyPivoter;

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
    /**
     * @var TaxonomyPivoter
     */
    protected $pivoter;

    /**
     * @var string
     */
    protected $taxonomy;

    /**
     * Reference inverse parent->child relationships if true.
     * @var bool
     */
    protected $inversePivot = false;

    /**
     * @var string
     */
    protected $inversePostType;

    /**
     * The post types we're relating.
     * @var mixed|string
     */
    protected $pivotPostType = 'post';

    /**
     * The post object is available after $this->render() is called.
     * @var \WP_Post
     */
    protected $post;

    /**
     * @var bool Allow multiple relations?
     */
    protected $multipleRelations = true;

    /**
     * @return \WP_Post[]
     */
    protected function getPivotablePosts()
    {
        $args = [
            'post_type' => $this->pivotPostType,
            'nopaging'  => true,
            'orderby'   => 'title',
            'order'     => 'asc'
        ];

        $query = new \WP_Query( $args );

        if ( $query->post_count > 0 ) {
            return $query->posts;
        }

        return [];
    }

    public function __construct( $args = [] )
    {
        parent::__construct( $args );

        if ( isset( $args['taxonomy'] ) ) {
            $this->taxonomy = $args['taxonomy'];
        }
        if ( isset( $args['inversePivot'] ) ) {
            $this->inversePivot = $args['inversePivot'];
        }
        if ( isset( $args['inversePostType'] ) ) {
            $this->inversePostType = $args['inversePostType'];
        }
        if ( isset( $args['pivotPostType'] ) ) {
            $this->pivotPostType = $args['pivotPostType'];
        }
        if ( isset( $args['multipleRelations'] ) ) {
            $this->multipleRelations = $args['multipleRelations'];
        }
    }

    public function getPostTypeLabels()
    {
        $object = get_post_type_object( $this->pivotPostType );

        return [
            'singular' => $object->labels->singular_name,
            'plural' => $object->labels->name
        ];
    }

    public function getArgs( \WP_Post $post )
    {

        return [
            'parentId' => $post->ID,
            'relatedPostIds' => $this->pivoter->getIds(),
            'pivotablePosts' => $this->getPivotablePosts(),
            'nonce' => wp_create_nonce( 'post-pivot-meta-box' ),
            'taxonomy' => $this->taxonomy,
            'relatedPostTypeObject' => get_post_type_object( $this->pivotPostType ),
            'multipleRelations' => $this->multipleRelations,
            'inversePivot' => $this->inversePivot,
            'inversePostType' => $this->inversePostType
        ];
    }

    protected function renderViewControls()
    {
        ?>
        <div class="pull-left">

        </div>

        <div class="pull-right">

            Filter <span data-bind="text: relatedPostsLabel">Posts</span>:

            <div class="btn-group">
                <button type="button" class="btn btn-xs" data-bind="
                    css: { 'btn-primary active': 'all' == filter() },
                    click: function(){ filter('all'); }">
                    All
                </button>
                <button type="button" class="btn btn-xs" data-bind="
                    css: { 'btn-primary active': 'attached' == filter() },
                    click: function(){ filter('attached'); }">
                    Attached
                </button>
                <button type="button" class="btn btn-xs" data-bind="
                    css: { 'btn-primary active': 'unattached' == filter() },
                    click: function(){ filter('unattached'); }">
                    Unattached
                </button>
            </div>
        </div>

        <div class="clearfix"></div>
        <?php
    }

    public function renderListView()
    {
        ?>
        <!-- ko if: 'list' == view() -->
        <ul class="list-group">
            <!-- ko foreach: filteredPosts -->
            <li class="list-group-item" data-bind="
                css: { 'list-group-item-success': $parent.isAttached( $data ) }
                ">

                <span data-bind="text: $data.post_title"></span>
                <a href="#" data-bind="attr: { href: '<?php echo admin_url(); ?>post.php?action=edit&post=' + $data.ID }" target="_blank">[edit]</a>

                <div class="pull-right">

                    <div class="btn-group">

                        <!-- ko if: $parent.isAttached($data) -->
                        <button type="button" class="btn btn-xs" data-bind="
                            click: function(){ $parent.detachPost(this); },
                            css: { 'btn-primary active': $parent.isAttached($data) },
                            disable: $data.busy()
                            ">
                            <!-- ko if: ! $data.busy() -->
                            Detach
                            <!-- /ko -->
                            <!-- ko if: $data.busy() -->
                            <i class="fa fa-spinner fa-spin"></i>
                            Detaching
                            <!-- /ko -->
                        </button>
                        <!-- /ko -->

                        <!-- ko if: ! $parent.isAttached($data) -->
                        <button type="button" class="btn btn-xs" data-bind="
                            click: function(){ $parent.attachPost(this); },
                            disable: $data.busy()
                            ">
                            <!-- ko if: ! $data.busy() -->
                            Attach
                            <!-- /ko -->
                            <!-- ko if: $data.busy() -->
                            <i class="fa fa-spinner fa-spin"></i>
                            Attaching
                            <!-- /ko -->
                        </button>
                        <!-- /ko -->
                    </div>

                </div>
            </li>
            <!-- /ko -->
        </ul>
        <!-- /ko -->

        <?php if ( ! $this->multipleRelations ) : ?>
        <small>
            <i>
                Attaching another <?php echo $this->getPostTypeLabels()['singular']; ?>
                will replace any existing current attachment.
            </i>
        </small>
        <?php endif; ?>
        <?php
    }

    public function render( \WP_Post $post )
    {
        // A taxonomy is required.
        if ( ! $this->taxonomy || empty( $this->taxonomy ) ) {
            echo '<p>No taxonomy was specified for pivoting posts.</p>';
            return;
        }

        // Store the post object
        $this->post = $post;

        // Enqueue the view model javascript.
        wp_enqueue_script( 'wp-orbit-posts-pivoter-view-model' );

        // Taxonomy.
        $taxonomy = $this->taxonomy;

        // Instantiate a post pivoter.
        $this->pivoter = new TaxonomyPivoter( $post->ID, $taxonomy );
        $this->pivoter->pivotPosts( $this->pivotPostType );

        if ( $this->inversePivot ) {
            $this->pivoter->inversePivot( $post->post_type );
        }

        ?>

        <div class="hide-on-load">
            <i class="fa fa-spinner spin"></i>
            Loading...
        </div>

        <div class="show-on-load">
            <div class="wpbs">
                <?php $this->renderViewControls(); ?>

                <hr>

                <?php $this->renderListView(); ?>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                ko.applyBindings(
                    new PostsPivoterViewModel( <?php echo json_encode( $this->getArgs( $post ) ); ?> ),
                    document.getElementById( '<?php echo $this->getId(); ?>' )
                )
            });
        </script>
        <?php
    }

    public function save( $postId, $postType )
    {
        parent::save( $postId, $postType );
    }
}