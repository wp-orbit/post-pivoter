interface PostsPivoterViewModelArgs
{
    parentId: any;
    pivotablePosts: any;
    relatedPostIds: any;
    nonce: string;
    taxonomy: string;
    relatedPostTypeObject: any;
}

class PostsPivoterViewModel
{
    busy = ko.observable(false);
    parentId: Number;
    nonce: string;
    taxonomy: string;

    view = ko.observable('list');
    filter = ko.observable('all');

    posts = ko.observableArray([]);

    relatedPostsLabel = ko.observable('Posts');

    allPosts = ko.pureComputed(() => {
        return this.posts();
    });

    attachedPostIds = ko.observableArray([]);

    attachPost(post)
    {
        let postId = String( post.ID );

        post.busy(true);

        OrbitAjax.post( 'orbit_posts_pivoter_attach_post', {
            postId: this.parentId,
            attach: postId,
            nonce: this.nonce,
            taxonomy: this.taxonomy
        }, (r) => {
            // Add the post ID to the attach post IDs observable array.
            this.attachedPostIds.push( postId );
            post.busy(false);
        }, (r) => {
            console.log(r);
            post.busy(false);
        });
    }

    detachPost(post)
    {
        let postId = String(post.ID);

        post.busy(true);

        OrbitAjax.post('orbit_posts_pivoter_detach_post', {
            postId: this.parentId,
            detach: postId,
            nonce: this.nonce,
            taxonomy: this.taxonomy
        }, (r) => {
            // Remove the post ID to the attach post IDs observable array.
            this.attachedPostIds.remove(postId);
            post.busy(false);
        }, (r) => {
            post.busy(false);
        });
    }

    isAttached(post) {
        return -1 != this.attachedPostIds.indexOf( String( post.ID ) );
    }

    /** Add some observables to post models. */
    preparePost(post) {
        post.busy = ko.observable(false);
    }

    constructor(args: PostsPivoterViewModelArgs)
    {
        // Set the parent's post ID-- this is the post to which we are attaching posts.
        this.parentId = args.parentId;
        // Set the security token for making ajax requests.
        this.nonce = args.nonce;
        // Set the taxonomy we're working with for establishing post relationships.
        this.taxonomy = args.taxonomy;

        // Prepare posts before injecting to view model.
        _.each( args.pivotablePosts, this.preparePost );
        // Inject all available post models.
        this.posts( args.pivotablePosts );

        // Preload attached post IDs.
        this.attachedPostIds( args.relatedPostIds );
        // Set the related post type plural label.
        this.relatedPostsLabel(args.relatedPostTypeObject.labels.name);
    }
}
