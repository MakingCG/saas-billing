<?php
namespace VueFileManager\Subscription\Domain\Plans\DTO;

use Illuminate\Database\Eloquent\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class CreateMeteredPlanData extends DataTransferObject
{
    public array|Collection $meters;
    public ?string $description = null;
    public string $currency;
    public string $name;
    public string $type;

    public static function fromRequest($request): self
    {
        return new self([
            'description' => $request->input('description'),
            'currency'    => $request->input('currency'),
            'meters'      => $request->input('meters'),
            'name'        => $request->input('name'),
            'type'        => $request->input('type'),
        ]);
    }

    public static function fromArray(array $array): self
    {
        return new self([
            'type'        => $array['type'],
            'name'        => $array['name'],
            'meters'      => $array['meters'],
            'currency'    => $array['currency'],
            'description' => $array['description'],
        ]);
    }
}
