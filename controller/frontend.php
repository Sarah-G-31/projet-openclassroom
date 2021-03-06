<?php
require_once('model/PostManager.php');
require_once('model/CommentManager.php');
require_once('model/MemberManager.php');

function posts() {
    $postManager = new PostManager();
    $posts = $postManager->postsList();

    require('view/frontend/postsListView.php');
}

function comments() {
    $postManager = new PostManager();
    $commentManager = new CommentManager();

    $post = $postManager->post($_GET['id']);
    $comments = $commentManager->getComments($_GET['id']);

    if (empty($post['id'])) { 
        throw new Exception('Ce billet n\'existe pas !');
    } 
    else {
        require('view/frontend/commentView.php');
    }
}

function addComment($postId, $memberId, $comment) {
    $commentManager = new CommentManager();
    $affectedLines = $commentManager->postComment($postId, $memberId, $comment);

    if ($affectedLines === false) {
        throw new Exception('Impossible d\'ajouter le commentaire !');
    }
    else {
        header('Location: index.php?action=post&id=' . $postId);
    }
}

function report($postId, $commentId) {
    $commentManager = new CommentManager();
    $report = $commentManager->postReport($commentId);

    header("Location: index.php?action=post&id=$postId");
}

function registration() {
    $errors = array();
    $input_value = array();

    // Contrôle du pseudo
    if (!empty($_POST['pseudo'])) {
        $pseudo = strip_tags($_POST['pseudo']);
        $input_value['pseudo'][] = $pseudo;
        if (strlen($pseudo) < 3 ) { $errors['pseudo'][] = "Pseudo trop court"; }
        if (strlen($pseudo) > 12 ) { $errors['pseudo'][] = "Pseudo trop long"; }
        if (preg_match("#[^-_ A-Za-z0-9]#", $pseudo)) { $errors['pseudo'][] = "Caractère interdit"; }
    }
    else { $errors['pseudo'][] = "Pseudo obligatoire"; }

    // Contrôle des mots de passe
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];
    if (empty($password1)) { $errors['password1'][] = "Le mot de passe est obligatoire"; }
    if (strlen($password1) < 6) { $errors['password1'][] = "Mot de passe trop court"; }
    if (strlen($password2) == 0) { $errors['password1'][] = "Le mot de passe doit etre saisi 2 fois"; }
    if ((!preg_match("#[0-9]+#", $password1)) || (!preg_match("#[0-9]+#", $password2))) { $errors['password1'][] = "Le mot de passe ne contient aucun chiffre"; }
    if ($password1 != $password2) { $errors['password1'][] = "Les mots de passe saisis sont différents";
    }
    else { $hashed_password = password_hash($_POST['password1'], PASSWORD_DEFAULT); }
    
    // Contrôle de l'email
    if (!empty($_POST['email'])) {
        $email = htmlspecialchars($_POST['email']);
        $input_value['email'][] = $email;
        if (!preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email)) {
            $errors['email'][] = "Adresse email invalide";
        }
    } 
    else { $errors['email'][] = "Adresse email obligatoire"; }


    $_SESSION["input_value"] = $input_value;
    $_SESSION["errors"] = $errors;
    header("location: index.php?action=registration");

    if (count($errors) == 0) {
        $MemberManager = new MemberManager();
        $pseudoControl = $MemberManager->getPseudo($pseudo);

        if (count($pseudoControl['pseudo']) > 0 ) {
            $errors['pseudo'][] = "Ce pseudo existe déjà";
            $_SESSION["errors"] = $errors;
            header("location: index.php?action=registration");
        }
    
        $MemberManager = new MemberManager();
        $emailControl = $MemberManager->getEmail($email);

        if (count($emailControl['email']) > 0 ) {
            $errors['email'][] = "Cet email est déjà enregistré";
            $_SESSION["errors"] = $errors;
            header("location: index.php?action=registration");
        }
        
        if (count($errors) == 0) {
            $MemberManager = new MemberManager();
            $req = $MemberManager->addMember($pseudo, $hashed_password, $email);
            $req->closeCursor();
            session_destroy();
            header("Location: index.php?action=connection");
            exit();
        }
    }
}

function connection() {
    $errors = array();
    $input_value = array();

    // Contrôle du pseudo
    if (!empty($_POST['pseudo'])) {
        $pseudo = strip_tags($_POST['pseudo']);
        $input_value['pseudo'][] = $pseudo;
    }
    else {
        $errors['pseudo'][] = "Pseudo obligatoire";
    }

    // Contrôle du mots de passe
    $password = $_POST['password'];
    if (empty($password)) {
        $errors['password'][] = "Le mot de passe est obligatoire"; 
    }
    
    $_SESSION["input_value"] = $input_value;
    $_SESSION["errors"] = $errors;
    header("location: index.php?action=connection");

    if (count($errors) == 0) {

        $MemberManager = new MemberManager();
        $control = $MemberManager->connectionControl($pseudo);

        if (!$control)
        {
            $errors['password'][] = "Mauvais identifiant ou mot de passe !";
            $_SESSION["errors"] = $errors;
            header("location: index.php?action=connection");
        }
        else if (password_verify($password, $control['password']))
        {
            $_SESSION['id'] = $control['id'];
            $_SESSION['pseudo'] = $pseudo;
            $_SESSION['admin'] = $control['admin'];
            header("location: index.php");
        }
        else
        {
            $errors['password'][] = "Mauvais identifiant ou mot de passe !";
            $_SESSION["errors"] = $errors;
            header("location: index.php?action=connection");
        }        
    }
}

function disconnection() {
    $_SESSION = array();
    session_destroy();

    header("Location: index.php");
}