<?php

return [
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'email' => ':attributeはメール形式で入力してください。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
        'numeric' => ':attributeは:max以下で指定してください。',
        'array' => ':attributeは:max個以内で選択してください。',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください。',
        'numeric' => ':attributeは:min以上で指定してください。',
        'array' => ':attributeは:min個以上選択してください。',
    ],
    'size' => [
        'string' => ':attributeは:size桁で入力してください。',
    ],
    'unique' => 'その:attributeは既に使用されています。',
    'confirmed' => ':attributeと一致しません。',
    'url' => ':attributeは有効なURL形式で入力してください。',
    'date' => ':attributeは有効な日付形式で入力してください。',
    'integer' => ':attributeは整数で指定してください。',
    'array' => ':attributeを選択してください。',
    'exists' => '指定された:attributeは存在しません。',

    // 項目名の日本語化
    'attributes' => [
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'title' => 'タイトル',
        'author' => '著者名',
        'isbn' => 'ISBN',
        'published_date' => '出版日',
        'description' => '説明',
        'image_url' => '画像URL',
        'genres' => 'ジャンル',
        'rating' => '評価',
        'comment' => 'コメント',
    ],
];
