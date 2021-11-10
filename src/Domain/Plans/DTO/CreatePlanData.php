<?php
namespace VueFileManager\Subscription\Domain\Plans\DTO;

use Illuminate\Database\Eloquent\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class CreatePlanData extends DataTransferObject
{
    public string $name;
    public int $amount;
    public string $interval;
    public string $description;
    public array|Collection $features;

    public static function fromRequest($request): self
    {
        return new self([
            'name'        => $request->input('name'),
            'amount'      => $request->input('amount'),
            'interval'    => $request->input('interval'),
            'description' => $request->input('description'),
            'features'    => $request->input('features'),
        ]);
    }

    public static function fromArray(array $array): self
    {
        return new self([
            'name'        => $array['name'],
            'amount'      => $array['amount'],
            'interval'    => $array['interval'],
            'description' => $array['description'],
            'features'    => $array['features'],
        ]);
    }
}
