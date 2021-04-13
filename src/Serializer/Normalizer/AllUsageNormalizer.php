<?php

namespace App\Serializer\Normalizer;

use App\Model\AllUsage;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class AllUsageNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface {
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ALL_USAGE_NORMALIZER_ALREADY_CALLED';

    public function __construct() {
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof AllUsage;
    }

    /**
     * @param AllUsage $object
     * @param null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = array()): array {

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);

        $years = [];
        foreach ($object->getYears() as $year) {
            $years[] = [
                'year' => $year->getYear(),
                'consume' => $year->getConsume()->getTotal(3),
                'generate' => $year->getGenerate()->getTotal(3),
            ];
        }

        $data['years'] = $years;

        return $data;
    }

    public function hasCacheableSupportsMethod(): bool {
        return false;
    }
}
