<?php
namespace VueFileManager\Subscription\Domain\Plans\DTO;

use Illuminate\Database\Eloquent\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class CreatePlanData extends DataTransferObject
{
    public string $name;
    public float $amount;
    public string $currency;
    public string $interval;
    public string $description;
    public array|Collection $features;

    public static function fromRequest($request): self
    {
        return new self([
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
            'name'        => $array['name'],
            'amount'      => $array['amount'],
            'currency'    => $array['currency'],
            'interval'    => $array['interval'],
            'features'    => $array['features'],
            'description' => $array['description'],
        ]);
    }
}
