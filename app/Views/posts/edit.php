<h1>Edit Post</h1>

<?= $renderPartial('posts/form', ['post' => $post]) ?>

<?= $linkTo('Show', "/posts/{$post->id}") ?> |
<?= $linkTo('Back', '/posts') ?>