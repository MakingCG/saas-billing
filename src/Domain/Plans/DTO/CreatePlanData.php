<?php
namespace VueFileManager\Subscription\Domain\Plans\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class CreatePlanData extends DataTransferObject
{
    public string $name;
    public int $price;
    public int $amount;
    public string $interval;
    public string $description;
    public array $features;

    public static function fromRequest($request): self
    {
        return new self([
            'name'        => $request->input('name'),
            'price'       => $request->input('price'),
            'amount'      => $request->input('amount'),
            'interval'    => $request->input('interval'),
            'description' => $request->input('description'),
            'features'    => $request->input('features'),
        ]);
    }
}
