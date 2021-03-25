<?php

namespace App\Serializer\Normalizer;

use App\Model\MonthUsage;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class MonthUsageNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface {
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'MONTH_USAGE_NORMALIZER_ALREADY_CALLED';
    private string $serializeDateFormat;

    public function __construct(string $serializeDateFormat) {
        $this->serializeDateFormat = $serializeDateFormat;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof MonthUsage;
    }

    public function normalize($object, $format = null, array $context = array()): array {

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);

        $days = [];
        foreach ($object->getDays() as $day) {
            $days[] = [
                'day' => (int)$day->getDate()->format('d'),
                'consume' => $day->getConsume()->getTotal(3),
                'generate' => $day->getGenerate()->getTotal(3),
            ];
        }

        $data['date'] = $object->getDate()->format('Y-m');
        $data['days'] = $days;

        return $data;
    }

    public function hasCacheableSupportsMethod(): bool {
        return false;
    }
}
