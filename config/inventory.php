<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Low-stock threshold
    |--------------------------------------------------------------------------
    |
    | Products with stock_on_hand strictly below this value are treated as
    | low stock. The GET /api/products/low-stock endpoint uses this default
    | when the request does not include a threshold query parameter.
    |
    */

    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),

];
