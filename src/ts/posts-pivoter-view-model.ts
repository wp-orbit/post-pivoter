interface PostsPivoterViewModelArgs
{
    parentId: any;
    pivotablePosts: any;
    relatedPostIds: any;
    nonce: string;
    taxonomy: string;
    relatedPostTypeObject: any;
    multipleRelations: boolean;
    inversePivot: boolean;
    inversePostType: any;
}

class PostsPivoterViewModel
{
    busy = ko.observable(false);
    parentId: Number;
    nonce: string;
    taxonomy: string;
    multipleRelations: boolean;
    inversePivot: boolean;
    inversePostType: any;

    view = ko.observable('list');
    filter = ko.observable('all');

    posts = ko.observableArray([]);

    relatedPostsLabel = ko.observable('Posts');

    allPosts = ko.pureComputed(() => {
        return this.posts();
    });

    filteredPosts = ko.pureComputed(() => {

        // Get all posts.
        let posts = this.posts();

        // Filter attached.
        if ( 'attached' == this.filter() ) {
            posts = this.filters.attached(posts);
        }
        // Filter unattached.
        if ( 'unattached' == this.filter() ) {
            posts = this.filters.unattached(posts);
        }

        return posts;
    });

    filters = {
        attached: (posts) => {
            return _.filter(posts, (post) => {
                return -1 != this.attachedPostIds.indexOf(String(post.ID));
            });
        },
        unattached: (posts) => {
            return _.filter(posts, (post) => {
                return -1 == this.attachedPostIds.indexOf(String(post.ID));
            });
        }
    };


    attachedPostIds = ko.observableArray([]);

    attachPost(post)
    {
        let postId = String( post.ID );

        post.busy(true);

        OrbitAjax.post( 'orbit_posts_pivoter_attach_post', {
            postId: this.parentId,
            attach: postId,
            nonce: this.nonce,
            taxonomy: this.taxonomy,
            multiple: this.multipleRelations,
            inversePivot: this.inversePivot,
            inversePostType: this.inversePostType
        }, (r) => {
            if ( false == this.multipleRelations ) {
                this.attachedPostIds([]);
            }
            // Add the post ID to the attach post IDs observable array.)
            this.attachedPostIds.push( postId );
            post.busy(false);
        }, (r) => {
            // console.log(r);
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
            taxonomy: this.taxonomy,
            inversePivot: this.inversePivot,
            inversePostType: this.inversePostType
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
        this.multipleRelations = args.multipleRelations;

        // Prepare posts before injecting to view model.
        _.each( args.pivotablePosts, this.preparePost );
        // Inject all available post models.
        this.posts( args.pivotablePosts );

        // Preload attached post IDs.
        let related = [];
        _.each( args.relatedPostIds, (id) => {
            related.push( String(id) );
        });
        this.attachedPostIds(related);

        // Set the related post type plural label.
        this.relatedPostsLabel(args.relatedPostTypeObject.labels.name);

        // Inverse post type.
        this.inversePivot = args.inversePivot;
        this.inversePostType = args.inversePostType;
    }
}
