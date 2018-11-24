<?php

$id_column_name  = "商品ID";
$url_column_name = "画像1";
$file_dir        = "images";

// csvファイル名取得
$csv_file = null;
if (isset($argv[1])) {
	$csv_file = $argv[1];
}
if (!file_exists($csv_file)) {
	echo "no csv file\n";
	exit;
}

// ディレクトリ作成
if (!file_exists("./" . $file_dir)) {
	if (!mkdir("./" . $file_dir)) {
		echo "mkdir error\n";
		exit;
	}
}

// ファイルオープン
$fp = fopen($csv_file, "r");
if ($fp === false) {
	echo "open error\n";
	exit;
}

$id_column_num  = null;
$url_column_num = null;
$before_id = "hogehogehoge";
$item_count = 0;

$i = 1;
while ($row = fgetcsv($fp)) {
	// 一行目
	if ($i <= 1) {
		foreach ($row as $key => $value) {
			if ($value == $id_column_name) {
				$id_column_num = $key;
			}
			if ($value == $url_column_name) {
				$url_column_num = $key;
			}
		}

		if ($id_column_num === null || $url_column_num === null) {
			echo "no column\n";
			exit;
		}

		$i++;
		continue;
	}

	// 値が入ってるか？
	if (empty($row[$id_column_num])) {
		echo $i . " no id\n";
		$i++;
		continue;
	}
	if (empty($row[$url_column_num])) {
		echo $i . " no url\n";
		$no_image_item_count++;
		$i++;
		continue;
	}

	// 同じidだったら次へ
	if ($before_id == $row[$id_column_num]) {
		$i++;
		continue;
	}

	$pathinfo = pathinfo($row[$url_column_num]);
	if (!isset($pathinfo['extension'])) {
		$pathinfo['extension'] = "";
		echo $i . " no ext\n";
	}

	exec("/usr/bin/curl -o " . "./" . $file_dir . "/" . $row[$id_column_num] . "." . $pathinfo['extension'] . " " . $row[$url_column_num]);

/*
	// httpsは取れないよ
	$row[$url_column_num] = str_replace("https", "http", $row[$url_column_num]);

	$data = file_get_contents($row[$url_column_num]);
	if ($data === false) {
		echo $i . " file get error\n";
		$i++;
		continue;
	}

	if (file_put_contents("./" . $file_dir . "/" . $row[$id_column_num] . "." . $pathinfo['extension'], $data) === false) {
		echo $i . " file put error\n";
		$i++;
		continue;
	}
*/

	if ($before_id != $row[$id_column_num]) {
		$item_count++;
		echo "item_count " . $item_count . "\n";
	}

	$before_id = $row[$id_column_num];

	echo $i . " finish\n";

//	if ($i >= 10) {
//		echo "stop\n";
//		exit;
//	}

	$i++;
}
