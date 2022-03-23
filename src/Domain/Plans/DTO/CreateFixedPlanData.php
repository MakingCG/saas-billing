<?php
namespace VueFileManager\Subscription\Domain\Plans\DTO;

use Illuminate\Database\Eloquent\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class CreateFixedPlanData extends DataTransferObject
{
    public string $type;
    public string $name;
    public float $amount;
    public string $currency;
    public string $interval;
    public ?string $description = null;
    public array|Collection $features;

    public static function fromRequest($request): self
    {
        return new self([
            'type'        => $request->input('type'),
            'name'        => $request->input('name'),
            'amount'      => $request->input('amount'),
            'currency'    => $request->input('currency'),
            'interval'    => $request->input('interval'),
            'features'    => $request->input('features'),
            'description' => $request->input('description'),
        ]);
    }

    public static function fromArray(array $array): self
    {
        return new self([
            'type'        => $array['type'],
            'name'        => $array['name'],
            'amount'      => $array['amount'],
            'currency'    => $array['currency'],
            'interval'    => $array['interval'],
            'features'    => $array['features'],
            'description' => $array['description'],
        ]);
    }
}
