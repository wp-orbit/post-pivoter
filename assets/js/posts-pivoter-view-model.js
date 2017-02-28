var PostsPivoterViewModel = (function () {
    function PostsPivoterViewModel(args) {
        var _this = this;
        this.busy = ko.observable(false);
        this.view = ko.observable('list');
        this.filter = ko.observable('all');
        this.posts = ko.observableArray([]);
        this.relatedPostsLabel = ko.observable('Posts');
        this.allPosts = ko.pureComputed(function () {
            return _this.posts();
        });
        this.filteredPosts = ko.pureComputed(function () {
            var posts = _this.posts();
            if ('attached' == _this.filter()) {
                posts = _this.filters.attached(posts);
            }
            if ('unattached' == _this.filter()) {
                posts = _this.filters.unattached(posts);
            }
            return posts;
        });
        this.filters = {
            attached: function (posts) {
                return _.filter(posts, function (post) {
                    return -1 != _this.attachedPostIds.indexOf(String(post.ID));
                });
            },
            unattached: function (posts) {
                return _.filter(posts, function (post) {
                    return -1 == _this.attachedPostIds.indexOf(String(post.ID));
                });
            }
        };
        this.attachedPostIds = ko.observableArray([]);
        // Set the parent's post ID-- this is the post to which we are attaching posts.
        this.parentId = args.parentId;
        // Set the security token for making ajax requests.
        this.nonce = args.nonce;
        // Set the taxonomy we're working with for establishing post relationships.
        this.taxonomy = args.taxonomy;
        this.multipleRelations = args.multipleRelations;
        // Prepare posts before injecting to view model.
        _.each(args.pivotablePosts, this.preparePost);
        // Inject all available post models.
        this.posts(args.pivotablePosts);
        // Preload attached post IDs.
        this.attachedPostIds(args.relatedPostIds);
        // Set the related post type plural label.
        this.relatedPostsLabel(args.relatedPostTypeObject.labels.name);
    }
    PostsPivoterViewModel.prototype.attachPost = function (post) {
        var _this = this;
        var postId = String(post.ID);
        post.busy(true);
        OrbitAjax.post('orbit_posts_pivoter_attach_post', {
            postId: this.parentId,
            attach: postId,
            nonce: this.nonce,
            taxonomy: this.taxonomy,
            multiple: this.multipleRelations
        }, function (r) {
            if (false == _this.multipleRelations) {
                _this.attachedPostIds([]);
            }
            // Add the post ID to the attach post IDs observable array.)
            _this.attachedPostIds.push(postId);
            post.busy(false);
        }, function (r) {
            console.log(r);
            post.busy(false);
        });
    };
    PostsPivoterViewModel.prototype.detachPost = function (post) {
        var _this = this;
        var postId = String(post.ID);
        post.busy(true);
        OrbitAjax.post('orbit_posts_pivoter_detach_post', {
            postId: this.parentId,
            detach: postId,
            nonce: this.nonce,
            taxonomy: this.taxonomy
        }, function (r) {
            // Remove the post ID to the attach post IDs observable array.
            _this.attachedPostIds.remove(postId);
            post.busy(false);
        }, function (r) {
            post.busy(false);
        });
    };
    PostsPivoterViewModel.prototype.isAttached = function (post) {
        return -1 != this.attachedPostIds.indexOf(String(post.ID));
    };
    /** Add some observables to post models. */
    PostsPivoterViewModel.prototype.preparePost = function (post) {
        post.busy = ko.observable(false);
    };
    return PostsPivoterViewModel;
}());
