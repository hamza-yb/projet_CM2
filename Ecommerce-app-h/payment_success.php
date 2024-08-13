<?php

session_start();

if (!isset($_SESSION["uid"])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET["st"])) {

    $trx_id = $_GET["tx"] ?? null;
    $p_st = $_GET["st"] ?? null;
    $amt = $_GET["amt"] ?? null;
    $cc = $_GET["cc"] ?? null;
    $cm_user_id = $_GET["cm"] ?? null;
    $c_amt = $_COOKIE["ta"] ?? null;

    if ($p_st === "Completed" && $trx_id && $cm_user_id) {

        include_once("db.php");

        $sql = "SELECT p_id, qty FROM cart WHERE user_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $cm_user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $product_id[] = $row["p_id"];
                $qty[] = $row["qty"];
            }

            for ($i = 0; $i < count($product_id); $i++) {
                $sql = "INSERT INTO orders (user_id, product_id, qty, trx_id, p_status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("iiiss", $cm_user_id, $product_id[$i], $qty[$i], $trx_id, $p_st);
                $stmt->execute();
            }

            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $cm_user_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo <<<HTML
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Ecommerce</title>
                    <link rel="stylesheet" href="css/bootstrap.min.css"/>
                    <script src="js/jquery2.js"></script>
                    <script src="js/bootstrap.min.js"></script>
                    <script src="main.js"></script>
                    <style>
                        table tr td {padding:10px;}
                    </style>
                </head>
                <body>
                    <div class="navbar navbar-inverse navbar-fixed-top">
                        <div class="container-fluid">    
                            <div class="navbar-header">
                                <a href="#" class="navbar-brand">Ecommerce</a>
                            </div>
                            <ul class="nav navbar-nav">
                                <li><a href="index.php"><span class="glyphicon glyphicon-home"></span>Home</a></li>
                                <li><a href="profile.php"><span class="glyphicon glyphicon-modal-window"></span>Product</a></li>
                            </ul>
                        </div>
                    </div>
                    <p><br/></p>
                    <p><br/></p>
                    <p><br/></p>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-8">
                                <div class="panel panel-default">
                                    <div class="panel-heading"></div>
                                    <div class="panel-body">
                                        <h1>Merci beaucoup</h1>
                                        <hr/>
                                        <p>Bonjour <b>{$_SESSION["name"]}</b>, votre paiement a été effectué avec succès. Votre identifiant de transaction est <b>{$trx_id}</b>.<br/>
                                        Vous pouvez continuer vos achats.<br/></p>
                                        <a href="index.php" class="btn btn-success btn-lg">Continuer vos achats</a>
                                    </div>
                                    <div class="panel-footer"></div>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </body>
                </html>
                HTML;
            } else {
                header("Location: index.php");
            }
        }
    }
}
?>
