<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Report;

use Magento\Framework\App\RequestInterface;

class DataFilterProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getFromDate(): ?string
    {
        return $this->format($this->request->getParam('from_date', ''));
    }

    public function getToDate(): ?string
    {
        return $this->format($this->request->getParam('to_date', ''), true);
    }

    private function format(string $date, $isDayEnd = false): ?string
    {
        if (!$date) {
            return null;
        }
        $dateObj = new \DateTime($date);
        if ($isDayEnd) {
            $dateObj->setTime(23, 59, 59);
        } else {
            $dateObj->setTime(0, 0, 0);
        }

        return $dateObj->format('Y-m-d H:i:s');
    }
}
