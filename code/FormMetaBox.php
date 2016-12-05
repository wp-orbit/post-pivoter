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
    }

    /**
     * Call Form->savePostMeta() to handle the meta box callback.
     * @param $postId
     */
    public function save($postId)
    {
        $this->form->savePostMeta($postId);
    }

    /**
     * Call Form->loadPostMeta() to preload meta box values.
     * @param $post
     */
    public function render($post)
    {
        $this->form->loadPostMeta( $post->ID );
        $this->form->render();
    }
}