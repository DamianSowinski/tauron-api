<?php

namespace App\Serializer\Normalizer;

use App\Model\RangeUsage;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class RangeUsageNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface {
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'RANGE_USAGE_NORMALIZER_ALREADY_CALLED';
    private string $serializeDateFormat;

    public function __construct(string $serializeMonthDateFormat) {
        $this->serializeDateFormat = $serializeMonthDateFormat;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof RangeUsage;
    }

    /**
     * @param RangeUsage $object
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
                'month' => $month->getDate()->format($this->serializeDateFormat),
                'consume' => $month->getConsume()->getTotal(3),
                'generate' => $month->getGenerate()->getTotal(3),
            ];
        }

        $data['startDate'] = $object->getStartDate()->format($this->serializeDateFormat);
        $data['endDate'] = $object->getEndDate()->format($this->serializeDateFormat);
        $data['months'] = $months;

        return $data;
    }

    public function hasCacheableSupportsMethod(): bool {
        return false;
    }
}
