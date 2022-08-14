<?php

use Dedoc\Documentor\Support\Generator\OpenApi;
use Dedoc\Documentor\Support\PhpDoc;
use Dedoc\Documentor\Support\ResponseExtractor\JsonResourceResponseExtractor;
use Dedoc\Documentor\Support\TypeHandlers\TypeHandlers;
use Illuminate\Http\Resources\Json\JsonResource;
use function Spatie\Snapshots\assertMatchesSnapshot;

/*
dump([$phpDoc => optional($result)->toArray()]);

expect(true)->toBeTrue();
return;
 */

function getTypeFromDoc (string $phpDoc) {
    $docNode = PhpDoc::parse($phpDoc);
    $varNode = $docNode->getVarTagValues()[0];

    return TypeHandlers::handle($varNode->type);
}

it('handles simple types', function ($phpDoc) {
    $result = getTypeFromDoc($phpDoc);

    assertMatchesSnapshot($result ? $result->toArray() : null);
})->with([
    '/** @var string */',
    '/** @var int */',
    '/** @var integer */',
    '/** @var float */',
    '/** @var bool */',
    '/** @var boolean */',
    '/** @var true */',
    '/** @var false */',
    '/** @var float */',
    '/** @var double */',
    '/** @var scalar */',
    '/** @var array */',
    '/** @var null */',
    '/** @var object */',
]);

/**
 * @see https://phpstan.org/writing-php-code/phpdoc-types#general-arrays
 */
it('handles general arrays', function ($phpDoc) {
    $result = getTypeFromDoc($phpDoc);

    assertMatchesSnapshot($result ? $result->toArray() : null);
})->with([
    '/** @var string[] */',
    '/** @var array<string> */',
    '/** @var array<int, string> */',
    '/** @var array<string, string> */',
]);

it('handles shape arrays', function ($phpDoc) {
    $result = getTypeFromDoc($phpDoc);

    assertMatchesSnapshot($result ? $result->toArray() : null);
})->with([
    '/** @var array{string} */',
    '/** @var array{int, string} */',
    '/** @var array{0: string, 1: string} */',
    '/** @var array{wow: string} */',
    '/** @var array{test: string, wow?: string} */', // test var here is added so snapshot name generates correctly
    '/** @var array{string, string} */',
]);

it('determines type from phpdoc array', function () {
    $openApi = new OpenApi('3.1.0');
    $extractor = new JsonResourceResponseExtractor($openApi, TestResource::class);
    $type = $openApi->components->schemas[$extractor->extract()->getContent('application/json')->fullName]->type->properties['sample'];
    dd($openApi->components->toArray());
    expect(true)->toBeTrue();
})->skip();

class TestResource extends JsonResource {
    public function toArray($request)
    {
        return [
            /** @var array<string, DestResource> */
            'sample' => some_unparsable_method(),
        ];
    }
}

class DestResource extends JsonResource {
    public function toArray($request)
    {
        return [
            'id' => 1,
        ];
    }
}