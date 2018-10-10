<?php
declare(strict_types=1);

namespace DomainShop\Controller;

use DomainShop\Application\RegisterDomainName;
use DomainShop\Entity\Order;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\MiddlewareInterface;

final class RegisterController implements MiddlewareInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /** @var RegisterDomainName */
    private $registerDomainName;

    public function __construct(
        RouterInterface $router,
        TemplateRendererInterface $renderer,
        RegisterDomainName $registerDomainName
    ) {
        $this->router = $router;
        $this->renderer = $renderer;
        $this->registerDomainName = $registerDomainName;
    }

    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $submittedData = $request->getParsedBody();
        $formErrors = [];

        if (!isset($submittedData['domain_name'])) {
            throw new \RuntimeException('No domain name provided');
        }
        if (!preg_match('/^\w+\.\w+$/', $submittedData['domain_name'])) {
            throw new \RuntimeException('Invalid domain name provided');
        }

        if (isset($submittedData['register'])) {
            if (empty($submittedData['name'])) {
                $formErrors['name'][] = 'Please fill in your name';
            }
            if (!filter_var($submittedData['email_address'], FILTER_VALIDATE_EMAIL)) {
                $formErrors['email_address'][] = 'Please fill in a valid email address';
            }

            if (empty($formErrors)) {
                /** @var Order $order */
                $order = ($this->registerDomainName)(
                    $submittedData['domain_name'],
                    $submittedData['name'],
                    $submittedData['email_address'],
                    $submittedData['currency']
                );

                return new RedirectResponse(
                    $this->router->generateUri(
                        'pay',
                        ['orderId' => $order->id()]
                    )
                );
            }
        }

        $response->getBody()->write($this->renderer->render('register.html.twig', [
            'domainName' => $submittedData['domain_name'],
            'formErrors' => $formErrors,
            'submittedData' => $submittedData,
        ]));

        return $response;
    }
}
