<?php if (!empty($post->errors())): ?>
    <div class="error">
        <h3>Please fix the following errors:</h3>
        <ul>
            <?php foreach ($post->errors() as $field => $errors): ?>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php 
    $formPath = $post->id ? "/posts/{$post->id}" : "/posts";
    $method = $post->id ? 'patch' : 'post';
?>

<form action="<?= $formPath ?>" method="post">
    <?php if ($method === 'patch'): ?>
        <input type="hidden" name="_method" value="patch">
    <?php endif; ?>
    
    <div class="field">
        <label for="post_title">Title</label>
        <input type="text" id="post_title" name="post[title]" value="<?= htmlspecialchars($post->title ?? '') ?>" required>
    </div>
    
    <div class="field">
        <label for="post_author">Author</label>
        <input type="text" id="post_author" name="post[author]" value="<?= htmlspecialchars($post->author ?? '') ?>" required>
    </div>
    
    <div class="field">
        <label for="post_content">Content</label>
        <textarea id="post_content" name="post[content]" rows="10" required><?= htmlspecialchars($post->content ?? '') ?></textarea>
    </div>
    
    <div class="field">
        <label>
            <input type="checkbox" name="post[published]" value="1" <?= $post->published ? 'checked' : '' ?>>
            Publish this post
        </label>
    </div>
    
    <div class="actions">
        <button type="submit">Save Post</button>
    </div>
</form>