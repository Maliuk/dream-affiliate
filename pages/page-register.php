<?php require_once 'header.php'; ?>

<?php
if (isset($_POST['reg_name']) && isset($_POST['reg_email']) && isset($_POST['reg_password']) && isset($_POST['reg_conf_password'])) {
    $errorConf = $userError = false;
    
    $name = $_POST['reg_name'];
    $uemail = $_POST['reg_email'];
    $password = $_POST['reg_password'];
    $conf_password = $_POST['reg_conf_password'];
    
    if ($password !== $conf_password) {
        $errorConf = true;
    }
    else {
        global $da;
        $user_id = $da->registerUser($name, $uemail, $password);
        
        if (gettype($user_id) != 'integer') {
            $userError = true;
        }
    } 
}
?>

<div class="da-container">
    <form action="" method="POST">
        <label for="reg_name">Username</label>
        <input type="text" name="reg_name" id="reg_name" value="<?php echo $name ?>" required />
        
        <label for="reg_email">Email</label>
        <input type="email" name="reg_email" id="reg_email" value="<?php echo $uemail ?>" required />

        <label for="reg_password">Password</label>
        <input <?php if ($errorConf) { echo 'class="da-conf-error"'; } ?> type="password" name="reg_password" id="reg_password" required />
        
        <label for="reg_conf_password">Confirm Password</label>
        <input <?php if ($errorConf) { echo 'class="da-conf-error"'; } ?> type="password" name="reg_conf_password" id="reg_conf_password" required />

        <input type="submit" value="Register" class="btn btn-primary" />
        
        <?php
        if ($userError) {
            foreach ($user_id->errors as $error) {
                foreach ($error as $msg) {
                    echo '<span class="da-error">' . $msg . '</span>';
                }
            }
        }
        ?>
    </form>
</div>

<?php require_once 'footer.php'; ?>