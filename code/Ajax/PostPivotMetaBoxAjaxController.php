<?php
namespace WPOrbit\MetaBoxes\Ajax;

use WPOrbit\Ajax\AjaxController;
use WPOrbit\Taxonomies\Pivoter\TaxonomyPivoter;

class PostPivotMetaBoxAjaxController extends AjaxController
{
    protected $actionPrefix = 'orbit_posts_pivoter_';

    protected $privateActions = [
        'attach_post',
        'detach_post'
    ];

    protected function validateRequest()
    {
        if ( ! isset( $_POST['data']['nonce'] ) )
        {
            $this->error( 'No security token supplied.' );
        }
        if ( ! wp_verify_nonce( $_POST['data']['nonce'], 'post-pivot-meta-box' ) )
        {
            $this->error( 'Invalid token supplied.' );
        }
    }

    public function attach_post()
    {
        $this->validateRequest();

        $postId = $_POST['data']['postId'];
        $attach = $_POST['data']['attach'];
        $taxonomy = $_POST['data']['taxonomy'];
        $multiple = $_POST['data']['multiple'];
        $inversePivot = $_POST['data']['inversePivot'];
        $inversePostType = $_POST['data']['inversePostType'];

        // Instantiate the pivoter.
        $pivoter = new TaxonomyPivoter( $postId, $taxonomy );

        // False.
        if ( 'false' != $inversePivot ) {
            $pivoter->inversePivot( $inversePostType );
        }

        // Unset old relations?
        if ( 'false' == $multiple ) {
            foreach( $pivoter->getIds() as $id ) {
                $pivoter->removeRelation( $id ) ;
            }
        }

        $pivoter->addRelation( $attach );

        $this->json( true );
    }

    public function detach_post()
    {
        $this->validateRequest();

        $postId = $_POST['data']['postId'];
        $detach = $_POST['data']['detach'];
        $taxonomy = $_POST['data']['taxonomy'];
        $inversePivot = $_POST['data']['inversePivot'];
        $inversePostType = $_POST['data']['inversePostType'];

        $pivoter = new TaxonomyPivoter( $postId, $taxonomy );

        // Inverse pivot.
        if ( 'false' != $inversePivot ) {
            $pivoter->inversePivot( $inversePostType );
        }

        $pivoter->removeRelation( $detach );

        $this->json( true );
    }
}