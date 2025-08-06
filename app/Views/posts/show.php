<article>
    <h1><?= htmlspecialchars($post->title) ?></h1>
    <p style="color: #666; font-size: 0.9rem;">
        By <?= htmlspecialchars($post->author) ?> on <?= $post->created_at ?>
        <?php if ($post->isPublished()): ?>
            <span style="color: green;">• Published</span>
        <?php else: ?>
            <span style="color: orange;">• Draft</span>
        <?php endif; ?>
    </p>
    
    <div style="margin: 2rem 0; line-height: 1.8;">
        <?= nl2br(htmlspecialchars($post->content)) ?>
    </div>
    
    <div style="margin: 2rem 0;">
        <?= $linkTo('Edit', "/posts/{$post->id}/edit", ['class' => 'btn']) ?>
        <?= $linkTo('Back to Posts', '/posts') ?>
    </div>
</article>

<section style="margin-top: 3rem;">
    <h2>Comments (<?= count($comments) ?>)</h2>
    
    <div style="background: #f5f5f5; padding: 1rem; margin: 1rem 0;">
        <h3>Add a Comment</h3>
        <form action="/posts/<?= $post->id ?>/comments" method="post">
            <div class="field">
                <label for="comment_author">Your Name</label>
                <input type="text" id="comment_author" name="comment[author]" required>
            </div>
            <div class="field">
                <label for="comment_content">Comment</label>
                <textarea id="comment_content" name="comment[content]" rows="4" required></textarea>
            </div>
            <input type="hidden" name="comment[post_id]" value="<?= $post->id ?>">
            <button type="submit">Post Comment</button>
        </form>
    </div>
    
    <?php if (empty($comments)): ?>
        <p>No comments yet. Be the first to comment!</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div style="background: white; padding: 1rem; margin: 1rem 0; border-left: 3px solid #c52f24;">
                <strong><?= htmlspecialchars($comment->author) ?></strong>
                <span style="color: #666; font-size: 0.9rem;">• <?= $comment->created_at ?></span>
                <p style="margin-top: 0.5rem;"><?= nl2br(htmlspecialchars($comment->content)) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>