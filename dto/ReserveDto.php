<?php
class ReserveDto {

    // --- DTO が保持する全フィールド プロパティ名のホワイトリスト---
    //必ずここを使う事。攻撃者がPOST（連想配列）を改ざんした場合に備え、「許可されたものだけをプロパティに新生させる」ため。
    public const FIELDS = [
        // --- 基本情報 ---
        'room_id',
        'year',
        'month',
        'day',
        'plan',

        // --- 部屋・プラン情報 ---
        'room_name',
        'plan_title',
        'number_OfRoom',
        'maxGuest_OfRoom',

        // --- 日付関連 ---
        'checkin_date',
        'checkout_date',
        'stay_nights',
        'maxStayNights',
        'maxDate',
        'maxYear',
        'maxMonth',
        'start_weekDay',

        // --- カレンダー関連 ---
        'marks',
        'days',
        'pricesAllPlan',
        'plansData',
        'selectedPlan',
        'prices',

        // --- ユーザー入力 ---
        'person',
        'user_name',
        'user_telphone',
        'user_address',
        'email',
        'comment',

        // --- 見積り ---
        'estimate',
        'total_price',

        // --- エラー（差し戻し用） ---
        'error',
        'message',
    ];

    // --- コンストラクタ：プロパティ名リストFIELDS に従って動的にプロパティを生やす ---
    public function __construct(array $data)
    {
        foreach (self::FIELDS as $field) {
            $this->$field = $data[$field] ?? null;
        }
    }

    // --- ビューに渡すための連想配列を生成 ---
    public function toViewData(): array
    {
        $viewData = [];

        foreach (self::FIELDS as $field) {
            $viewData[$field] = $this->$field;
        }

        return $viewData;
    }
}

