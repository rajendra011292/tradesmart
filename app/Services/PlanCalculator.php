<?php
namespace App\Services;

final class PlanCalculator
{
    // Bias → default risk% (tweak to taste)
    private const BIAS_RISK = [
        'very_bullish' => 3.00,
        'mild_bullish' => 2.00,
        'sideways'     => 2.00,
        'mild_bearish' => 1.00,
        'very_bearish' => 1.00,
    ];

    /**
     * Normalize & validate raw inputs. Returns [data, errors].
     */
    public static function validate(array $in): array
    {
        $e = [];

        // required
        foreach (['entry_price','stop_loss','target_price','capital_allocated','market_bias'] as $f) {
            if (!isset($in[$f]) || $in[$f] === '') $e[$f] = 'Required';
        }

        $entry  = isset($in['entry_price']) ? (float)$in['entry_price'] : 0.0;
        $stop   = isset($in['stop_loss']) ? (float)$in['stop_loss'] : 0.0;
        $target = isset($in['target_price']) ? (float)$in['target_price'] : 0.0;
        $alloc  = isset($in['capital_allocated']) ? (float)$in['capital_allocated'] : 0.0;

        if ($alloc <= 0) $e['capital_allocated'] = 'Must be > 0';
        if ($entry <= 0) $e['entry_price'] = 'Must be > 0';
        if ($stop  <= 0) $e['stop_loss']   = 'Must be > 0';
        if ($target<= 0) $e['target_price']= 'Must be > 0';
        if (\abs($entry - $stop) < 1e-9) $e['stop_loss'] = 'Stop cannot equal entry';

        // infer side (long/short) from target vs entry if not provided
        $side = $in['side'] ?? null;
        if ($side !== 'long' && $side !== 'short') {
            $side = ($target > $entry) ? 'long' : 'short';
        }

        // risk%
        $riskPercent = isset($in['risk_percent']) && $in['risk_percent'] !== ''
            ? (float)$in['risk_percent']
            : self::defaultRiskForBias($in['market_bias'] ?? 'sideways');

        if ($riskPercent <= 0) $e['risk_percent'] = 'Risk% must be > 0';

        // clamp decimals to sane precision
        $norm = [
            'entry_price'       => round($entry, 4),
            'stop_loss'         => round($stop, 4),
            'target_price'      => round($target, 4),
            'capital_allocated' => round($alloc, 2),
            'risk_percent'      => round($riskPercent, 2),
            'market_bias'       => $in['market_bias'] ?? 'sideways',
            'side'              => $side,
        ];

        return [$norm, $e];
    }

    public static function defaultRiskForBias(string $bias): float
    {
        return self::BIAS_RISK[$bias] ?? 2.00;
    }

    /**
     * Compute all derived fields. Returns assoc array ready to persist.
     * Assumes inputs are validated/normalized.
     */
    public static function computeAll(array $d): array
    {
        $entry  = (float)$d['entry_price'];
        $stop   = (float)$d['stop_loss'];
        $target = (float)$d['target_price'];
        $alloc  = (float)$d['capital_allocated'];
        $riskP  = (float)$d['risk_percent'];
        $side   = $d['side']; // 'long'|'short'

        // risk amount in currency
        $riskAmt = $alloc * ($riskP / 100.0);

        // per-unit risk
        $unitRisk = max(1e-9, ($side === 'long') ? ($entry - $stop) : ($stop - $entry));

        // position size (qty)
        $qty = $riskAmt / $unitRisk;

        // capital used ~ qty * entry (cap at allocated)
        $capUsed = min($alloc, $qty * $entry);

        // RR ratio
        $rewardPerUnit = ($side === 'long') ? ($target - $entry) : ($entry - $target);
        $rr = $rewardPerUnit / $unitRisk;

        // expected P/L
        $expProfit = $rewardPerUnit * $qty;
        $expLoss   = $unitRisk * $qty;

        return [
            'position_size'   => round($qty, 4),
            'capital_used'    => round($capUsed, 2),
            'rr_ratio'        => round($rr, 4),
            'expected_profit' => round($expProfit, 2),
            'expected_loss'   => round($expLoss, 2),
        ];
    }
}
