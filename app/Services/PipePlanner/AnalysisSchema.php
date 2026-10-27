<?php

namespace App\Services\PipePlanner;

use Illuminate\Support\Facades\Validator;

/**
 * The structured contract between the model and the application:
 * - jsonSchema() is sent to the model as its response_format.
 * - validate() is the server-side gate every response must pass before it is
 *   stored or shown, regardless of whether the provider enforced the schema.
 */
class AnalysisSchema
{
    public const OBSERVATION_TYPES = ['fixture', 'pipe_segment', 'connection_point', 'water_source', 'water_heater', 'valve', 'obstacle', 'other'];

    public const FITTING_CATEGORIES = ['elbow', 'tee', 'reducer', 'coupling', 'adapter', 'union', 'valve', 'end_cap', 'other'];

    public const NODE_KINDS = ['source', 'heater', 'main', 'branch_point', 'fixture'];

    public const ELEMENT_STATUSES = ['observed', 'proposed', 'unverified'];

    public const READINESS = ['ready_for_preliminary_quote', 'needs_more_information', 'requires_professional_review'];

    protected static function str(int $max = 500): array
    {
        return ['type' => 'string', 'maxLength' => $max];
    }

    protected static function nullable(array $schema): array
    {
        $schema['type'] = [$schema['type'], 'null'];

        return $schema;
    }

    protected static function obj(array $props): array
    {
        return ['type' => 'object', 'properties' => $props, 'required' => array_keys($props), 'additionalProperties' => false];
    }

    protected static function enum(array $values): array
    {
        return ['type' => 'string', 'enum' => $values];
    }

    public static function jsonSchema(): array
    {
        return self::obj([
            'observations' => ['type' => 'array', 'items' => self::obj([
                'id' => self::str(40),
                'image_index' => self::nullable(['type' => 'integer']),
                'type' => self::enum(self::OBSERVATION_TYPES),
                'label' => self::str(200),
                'basis' => self::enum(['observed', 'inferred']),
                'confidence' => self::enum(['high', 'medium', 'low']),
                'notes' => self::str(500),
            ])],
            'routing_options' => ['type' => 'array', 'items' => self::obj([
                'id' => self::str(40),
                'title' => self::str(150),
                'summary' => self::str(1000),
                'nodes' => ['type' => 'array', 'items' => self::obj([
                    'id' => self::str(40),
                    'kind' => self::enum(self::NODE_KINDS),
                    'label' => self::str(150),
                    'status' => self::enum(self::ELEMENT_STATUSES),
                ])],
                'segments' => ['type' => 'array', 'items' => self::obj([
                    'id' => self::str(40),
                    'from' => self::str(40),
                    'to' => self::str(40),
                    'line' => self::enum(['main', 'branch']),
                    'water' => self::enum(['cold', 'hot']),
                    'nominal_diameter_mm' => self::nullable(['type' => 'integer']),
                    'length_m' => self::nullable(['type' => 'number']),
                    'length_source' => self::enum(['customer', 'estimated', 'unknown']),
                    'customer_distance_id' => self::nullable(self::str(40)),
                    'status' => self::enum(self::ELEMENT_STATUSES),
                ])],
            ])],
            'fittings' => ['type' => 'array', 'items' => self::obj([
                'category' => self::enum(self::FITTING_CATEGORIES),
                'water' => self::nullable(self::enum(['cold', 'hot'])),
                'nominal_diameter_mm' => self::nullable(['type' => 'integer']),
                'secondary_diameter_mm' => self::nullable(['type' => 'integer']),
                'quantity' => self::nullable(['type' => 'integer']),
                'basis' => self::enum(['derived_from_route', 'assumption']),
                'notes' => self::str(300),
            ])],
            'product_suggestions' => ['type' => 'array', 'items' => self::obj([
                'product_id' => ['type' => 'integer'],
                'reason' => self::str(300),
            ])],
            'assumptions' => ['type' => 'array', 'items' => self::str(400)],
            'missing_information' => ['type' => 'array', 'items' => self::str(400)],
            'contradictions' => ['type' => 'array', 'items' => self::str(400)],
            'clarification_questions' => ['type' => 'array', 'items' => self::obj([
                'id' => self::str(40),
                'question' => self::str(400),
                'why' => self::str(400),
            ])],
            'safety_flags' => ['type' => 'array', 'items' => self::str(400)],
            'readiness' => self::enum(self::READINESS),
        ]);
    }

    /**
     * Decode and validate model output.
     *
     * @return array{data: ?array, errors: array}
     */
    public static function validate(string $raw): array
    {
        $raw = trim($raw);
        // Tolerate a fenced ```json block, nothing more lenient than that.
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $raw, $m)) {
            $raw = $m[1];
        }
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return ['data' => null, 'errors' => ['response is not valid JSON']];
        }

        $in = fn (array $v) => 'in:'.implode(',', $v);
        $validator = Validator::make($data, [
            'observations' => 'present|array|max:60',
            'observations.*.id' => 'required|string|max:40',
            'observations.*.image_index' => 'nullable|integer|min:0|max:50',
            'observations.*.type' => ['required', $in(self::OBSERVATION_TYPES)],
            'observations.*.label' => 'required|string|max:200',
            'observations.*.basis' => 'required|in:observed,inferred',
            'observations.*.confidence' => 'required|in:high,medium,low',
            'observations.*.notes' => 'present|nullable|string|max:500',
            'routing_options' => 'present|array|max:3',
            'routing_options.*.id' => 'required|string|max:40',
            'routing_options.*.title' => 'required|string|max:150',
            'routing_options.*.summary' => 'present|nullable|string|max:1000',
            'routing_options.*.nodes' => 'present|array|max:60',
            'routing_options.*.nodes.*.id' => 'required|string|max:40',
            'routing_options.*.nodes.*.kind' => ['required', $in(self::NODE_KINDS)],
            'routing_options.*.nodes.*.label' => 'required|string|max:150',
            'routing_options.*.nodes.*.status' => ['required', $in(self::ELEMENT_STATUSES)],
            'routing_options.*.segments' => 'present|array|max:120',
            'routing_options.*.segments.*.id' => 'required|string|max:40',
            'routing_options.*.segments.*.from' => 'required|string|max:40',
            'routing_options.*.segments.*.to' => 'required|string|max:40',
            'routing_options.*.segments.*.line' => 'required|in:main,branch',
            'routing_options.*.segments.*.water' => 'required|in:cold,hot',
            'routing_options.*.segments.*.nominal_diameter_mm' => 'nullable|integer|min:10|max:200',
            'routing_options.*.segments.*.length_m' => 'nullable|numeric|min:0|max:500',
            'routing_options.*.segments.*.length_source' => 'required|in:customer,estimated,unknown',
            'routing_options.*.segments.*.customer_distance_id' => 'nullable|string|max:40',
            'routing_options.*.segments.*.status' => ['required', $in(self::ELEMENT_STATUSES)],
            'fittings' => 'present|array|max:60',
            'fittings.*.category' => ['required', $in(self::FITTING_CATEGORIES)],
            'fittings.*.water' => 'nullable|in:cold,hot',
            'fittings.*.nominal_diameter_mm' => 'nullable|integer|min:10|max:200',
            'fittings.*.secondary_diameter_mm' => 'nullable|integer|min:10|max:200',
            'fittings.*.quantity' => 'nullable|integer|min:0|max:1000',
            'fittings.*.basis' => 'required|in:derived_from_route,assumption',
            'fittings.*.notes' => 'present|nullable|string|max:300',
            'product_suggestions' => 'present|array|max:60',
            'product_suggestions.*.product_id' => 'required|integer',
            'product_suggestions.*.reason' => 'present|nullable|string|max:300',
            'assumptions' => 'present|array|max:30',
            'assumptions.*' => 'string|max:400',
            'missing_information' => 'present|array|max:30',
            'missing_information.*' => 'string|max:400',
            'contradictions' => 'present|array|max:30',
            'contradictions.*' => 'string|max:400',
            'clarification_questions' => 'present|array|max:15',
            'clarification_questions.*.id' => 'required|string|max:40',
            'clarification_questions.*.question' => 'required|string|max:400',
            'clarification_questions.*.why' => 'present|nullable|string|max:400',
            'safety_flags' => 'present|array|max:20',
            'safety_flags.*' => 'string|max:400',
            'readiness' => ['required', $in(self::READINESS)],
        ]);

        if ($validator->fails()) {
            return ['data' => null, 'errors' => array_slice($validator->errors()->all(), 0, 20)];
        }

        // Keep only the validated keys; anything extra the model produced is discarded.
        return ['data' => $validator->validated(), 'errors' => []];
    }
}
