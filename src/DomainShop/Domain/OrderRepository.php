<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DomainShop\Domain;

use DomainShop\Entity\Order;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface OrderRepository
{
    public function nextId(): int;

    public function retrieve(int $orderId): Order;

    public function persist(Order $order): void;
}
