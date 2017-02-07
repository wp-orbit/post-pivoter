<?php
namespace WPOrbit\MetaBoxes;

use WPOrbit\Forms\Form;

/**
 * A metabox utilizing WPOrbit\Forms.
 *
 * Pass in a form and the regular MetaBox arguments, no other configuration is necessary.
 *
 * Class FormMetaBox
 * @package WPOrbit\MetaBoxes
 * @see \WPOrbit\Forms\Form
 */
class FormMetaBox extends MetaBox
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * FormMetaBox constructor.
     * @param Form $form
     * @param array $args, Fields: 'id', 'label', 'postTypes'
     */
    public function __construct(Form $form, $args = [])
    {
        parent::__construct($args);
        $this->form = $form;
        $this->form->setPostMetaContext();
    }

    /**
     * Call Form->savePostMeta() to handle the meta box callback.
     * @param $postId
     */
    public function save($postId)
    {
        $this->form->context->save($postId);
    }

    /**
     * Call Form->loadPostMeta() to preload meta box values.
     * @param $post
     */
    public function render( \WP_Post $post)
    {
        $this->form->context->load( $post->ID );
        $this->form->render();
    }
}