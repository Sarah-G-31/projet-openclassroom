<?php
$title = 'Mon Blog !';
ob_start(); ?>

<h1><?= $post['title']; ?></h1>

<a href="index.php"><h4>Retour à la liste des billets</h4></a>

<div class="news">
    <i> le <?= $post['date']; ?></i>
    <p class="test">
        <?= $post['content']; ?><br />
    </p>
</div>

<h2>Commentaires</h2>

<?php
while($comment = $comments->fetch())
{ ?>
    <div class="comment">
        <p><strong><?= $comment['author']; ?></strong> le <?= $comment['date']; ?></p>
        <p>
            <?= nl2br($comment['comment']); ?>
            <?php
            if (isset($_SESSION['admin'])) { ?>
                <a id="report" href='index.php?action=report&post=<?= $post['id']; ?>&comment=<?= $comment['id']; ?>'>Signaler</a><?php
            } ?>
        </p>
    </div><?php
} 
$comments->closeCursor();
?>

<form class="postCommentForm" action="index.php?action=addComment" method="post">
<p>
    <input type="text" placeholder="Pseudo" value="<?php if (isset($_SESSION['id']) AND isset($_SESSION['pseudo'])) { echo $_SESSION['pseudo']; } ?>" required><br />
    <input type="hidden" id="idPost" name="idPost" value="<?php if (isset($_GET['id'])) echo $_GET['id']; ?>">
    <input type="hidden" id="idMember" name="idMember" value="<?php if (isset($_SESSION['id'])) { echo $_SESSION['id']; } ?>">
    <textarea class="postComment" id="comment" name="comment" rows="7" cols="30" placeholder="votre message" required></textarea>
    <input type="submit" name="submit" value="Valider">
</p>
</form>

<?php
$content = ob_get_clean();
require('template.php');
?>


