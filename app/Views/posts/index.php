<h1>Blog Posts</h1>

<div style="margin-bottom: 2rem;">
    <?= $linkTo('New Post', '/posts/new', ['class' => 'btn']) ?>
</div>

<?php if (empty($posts)): ?>
    <p>No posts yet. Create your first post!</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #ddd;">
            <h2><?= $linkTo(htmlspecialchars($post->title), "/posts/{$post->id}") ?></h2>
            <p style="color: #666; font-size: 0.9rem;">
                By <?= htmlspecialchars($post->author) ?> on <?= $post->created_at ?>
            </p>
            <p><?= htmlspecialchars(substr($post->content, 0, 200)) ?>...</p>
            <div>
                <?= $linkTo('Read more', "/posts/{$post->id}") ?> |
                <?= $linkTo('Edit', "/posts/{$post->id}/edit") ?> |
                <form action="/posts/<?= $post->id ?>" method="post" style="display:inline">
                    <input type="hidden" name="_method" value="delete">
                    <button type="submit" onclick="return confirm('Are you sure?')" style="background:none;border:none;color:#c52f24;cursor:pointer;text-decoration:underline;">Delete</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>