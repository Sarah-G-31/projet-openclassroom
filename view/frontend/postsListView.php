<?php
$title = 'Mon Blog !';
ob_start(); ?>

<h1>Billet simple pour l'Alaska</h1>

<h4>Derniers billets du blog :</h4>

<?php
while($post = $posts->fetch()) 
{
?>
    <div class="news">
        <h3>
            <?= $post['title']; ?>
        </h3>
        <i>Publié le <?= $post['date']; ?></i>
        <p>
            <?= $post['content']; ?><br />
            <a href="index.php?action=post&amp;id=<?= $post['id']; ?>"><i>Commentaires</i></a>
        </p>
    </div><br />
<?php
}
$posts->closeCursor();
$content = ob_get_clean();
require('template.php');
?>
