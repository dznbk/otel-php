<?php
declare(strict_types=1);

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;

require __DIR__ . '/vendor/autoload.php';

// トレーサプロバイダからトレーサを生成
$tracer = Globals::tracerProvider()->getTracer('trace');

// トレーサでルートスパンを生成
$span = $tracer->spanBuilder('trace.php')
    ->startSpan();
// ルートスパンを有効にしてスパンを生成
$scope = $span->activate();

try {
    func1($tracer);
} catch (Throwable $e) {
        // 例外キャッチ時はルートスパンにエラー情報をセット
    $span->recordException($e)->setStatus(StatusCode::STATUS_ERROR);
    throw $e;
} finally {
    // 有効なスコープを終了
    $scope->detach();
    // ルートスパンを終了（送信）
    $span->end();
}

function func1(TracerInterface $tracer): void
{
    // 引数のトレーサで func1 スパンを生成
    $span = $tracer->spanBuilder('func1')->startSpan();

    // エラー時スパンを生成するため、20% で例外をスロー
    if (random_int(1, 5) === 1) {
        throw new Exception('error in func1');
    }

    $result = random_int(1, 100);
    // スパンに実行結果をアトリビュートにセット
    $span->setAttribute('result', $result);
        // func1 スパンを終了（送信）
    $span->end();
}

echo 'done';