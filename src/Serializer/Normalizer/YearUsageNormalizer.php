<?php

namespace App\Serializer\Normalizer;

use App\Model\YearUsage;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class YearUsageNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface {
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'YEAR_USAGE_NORMALIZER_ALREADY_CALLED';

    public function __construct() {
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof YearUsage;
    }

    /**
     * @param YearUsage $object
     * @param null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = array()): array {

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);

        $months = [];
        foreach ($object->getMonths() as $month) {
            $months[] = [
                'month' => (int)$month->getDate()->format('m'),
                'consume' => $month->getConsume()->getTotal(3),
                'generate' => $month->getGenerate()->getTotal(3),
            ];
        }

        $data['year'] = $object->getYear();
        $data['months'] = $months;

        return $data;
    }

    public function hasCacheableSupportsMethod(): bool {
        return false;
    }
}
