<!DOCTYPE html>
<html>
<head>
    <title>投票ページ</title>
    <style>
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        .item {
            flex: 0 0 10%;
            margin: 5px;
            padding: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .bar-container {
            display: flex;
            flex-direction: column-reverse;
            height: 200px;
            width: 20px;
            background-color: transparent;
            border: 1px solid #ccc;
        }
        .bar {
            background-color: lightblue;
            height: 0;
            transition: height 0.3s ease;
        }
        .img-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 5px;
        }
        .img-container img {
            max-width: 50px;
        }
        .voting-buttons {
            display: flex;
            flex-wrap: wrap;
        }
        .voting-button {
            flex: 0 0 50%;
            margin: 5px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        .vote-submit {
            padding: 10px;
            background-color: #4CAF50;
            border: none;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php
    // データベースへの接続情報
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "voting_db";

    // データベースへの接続
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 接続エラーの確認
    if ($conn->connect_error) {
        die("接続エラー: " . $conn->connect_error);
    }

    // 投票フォームが送信された場合
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["items"])) {
        $selectedItems = $_POST["items"];

        foreach ($selectedItems as $itemId) {
            // 選択された項目の投票数を1つ増やす
            $sql = "UPDATE items SET votes = votes + 1 WHERE id = " . (int)$itemId;
            $conn->query($sql);
        }
    }
    // 項目のリストを取得
    $sql = "SELECT id, name FROM items";
    $result = $conn->query($sql);

    $items = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }

    // データベース接続を閉じる
    $conn->close();

    // ランキングの投票が押された最新の日付を取得
    $voteDate = ""; // デフォルトは空文字列

    // データベースへの接続
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 接続エラーの確認
    if ($conn->connect_error) {
        die("接続エラー: " . $conn->connect_error);
    }

    // ランキングの投票の最新日を取得するクエリ
    $sql = "SELECT MAX(vote_date) AS latest_date FROM votes";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $voteDate = $row["latest_date"];
    }

    // データベース接続を閉じる
    $conn->close();
    ?>

    <h1>項目ランキング</h1>
    <h2>最新の投票日：<?php echo $voteDate; ?></h2>
    <div class="container">
        <?php
        // 投票数を取得して順位付け
        $rank = 1;
        foreach ($items as $item) {
            $votes = 0;
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("接続エラー: " . $conn->connect_error);
            }
            $sql = "SELECT SUM(votes) AS total_votes FROM votes WHERE item_id = {$item['id']}";
            $result = $conn->query($sql);
            if ($result) {
                $row = $result->fetch_assoc();
                $votes = $row["total_votes"];
            }
            $conn->close();

            if ($votes > 0) :
        ?>
                <div class="item">
                    <div class="bar-container">
                        <div class="bar" style="height: <?php echo $votes * 10; ?>px;"></div>
                    </div>
                    <div class="img-container">
                        <?php if ($rank <= 3): ?>
                            <img src="rank_<?php echo $rank; ?>.png" alt="Rank <?php echo $rank; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="item-name"><?php echo $item['name']; ?></div>
                </div>
        <?php
                $rank++;
            endif;
        }
        ?>
    </div>

    <h2>投票する項目</h2>
    <form method="post" action="">
        <div class="voting-buttons">
            <?php foreach ($items as $item): ?>
                <label class="voting-button">
                    <input type="checkbox" name="items[]" value="<?php echo $item['id']; ?>">
                    <?php echo $item['name']; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <br>
        <input type="submit" value="投票" class="vote-submit">
    </form>
</body>
</html>
