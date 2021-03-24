<?php

namespace App\Serializer\Normalizer;

use App\Model\DayUsage;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DayUsageNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface {
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'DAY_USAGE_NORMALIZER_ALREADY_CALLED';
    private string $serializeDateFormat;

    public function __construct(string $serializeDateFormat) {
        $this->serializeDateFormat = $serializeDateFormat;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof DayUsage;
    }

    public function normalize($object, $format = null, array $context = array()): array {

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['date'] = $object->getDate()->format($this->serializeDateFormat);

        return $data;
    }

    public function hasCacheableSupportsMethod(): bool {
        return false;
    }
}
