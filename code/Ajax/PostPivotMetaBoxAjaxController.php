<?php
namespace WPOrbit\MetaBoxes\Ajax;

use WPOrbit\Ajax\AjaxController;
use WPOrbit\Taxonomies\TaxonomyPivoter;

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

        $pivoter = new TaxonomyPivoter( $postId, $taxonomy );
        $pivoter->addRelation( $attach );

        $this->json( true );
    }

    public function detach_post()
    {
        $this->validateRequest();

        $postId = $_POST['data']['postId'];
        $detach = $_POST['data']['detach'];
        $taxonomy = $_POST['data']['taxonomy'];

        $pivoter = new TaxonomyPivoter( $postId, $taxonomy );
        $pivoter->removeRelation( $detach );

        $this->json( true );
    }
}