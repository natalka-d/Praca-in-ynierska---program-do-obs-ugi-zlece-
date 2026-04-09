<?php
namespace app\forms;

class OrderStageForm {
    // ID zamówienia
    public $order_id;

    // Etapy jako tablica wielowymiarowa: digital, offset, wide, binding, laser
    public $stages = [
        'digital' => [
            'stage_type' => 'digital',
            'copies' => 0,
            'description' => ''
        ],
        'offset' => [
            'stage_type' => 'offset',
            'total_sheets' => 0,
            'paper_type' => '',
            'description' => ''
        ],
        'wide' => [
            'stage_type' => 'wide',
            'description' => ''
        ],
        'binding' => [
            'stage_type' => 'binding',
            'description' => ''
        ],
        'laser' => [
            'stage_type' => 'laser',
            'description' => ''
        ]
    ];
}
