<?php declare(strict_types=1);

namespace DOMJudgeBundle\Controller\Jury;

use Doctrine\ORM\EntityManagerInterface;
use DOMJudgeBundle\Controller\BaseController;
use DOMJudgeBundle\Entity\Language;
use DOMJudgeBundle\Form\Type\PrintType;
use DOMJudgeBundle\Service\DOMJudgeService;
use DOMJudgeBundle\Utils\Printing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PrintController
 *
 * @Route("/jury/print")
 * @Security("has_role('ROLE_JURY') or has_role('ROLE_BALLOON')")
 *
 * @package DOMJudgeBundle\Controller\Jury
 */
class PrintController extends BaseController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var DOMJudgeService
     */
    protected $DOMJudgeService;

    /**
     * PrintController constructor.
     * @param EntityManagerInterface $entityManager
     * @param DOMJudgeService        $DOMJudgeService
     */
    public function __construct(EntityManagerInterface $entityManager, DOMJudgeService $DOMJudgeService)
    {
        $this->entityManager   = $entityManager;
        $this->DOMJudgeService = $DOMJudgeService;
    }

    /**
     * @Route("", name="jury_print")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function showAction(Request $request)
    {
        if (!$this->DOMJudgeService->dbconfig_get('enable_printing', 0)) {
            throw new AccessDeniedHttpException("Printing disabled in config");
        }

        $form = $this->createForm(PrintType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /** @var UploadedFile $file */
            $file             = $data['code'];
            $realfile         = $file->getRealPath();
            $originalfilename = $file->getClientOriginalName() ?? '';

            $langid   = $data['langid'];
            $username = $this->getUser()->getUsername();

            // Since this is the Jury interface, there's not necessarily a
            // team involved; do not set a teamname or location.
            $ret = Printing::send($realfile, $originalfilename, $langid, $username, "");

            return $this->render('@DOMJudge/jury/print_result.html.twig', [
                'success' => $ret[0],
                'output' => $ret[1],
            ]);
        }

        /** @var Language[] $languages */
        $languages = $this->entityManager->createQueryBuilder()
            ->from('DOMJudgeBundle:Language', 'l')
            ->select('l')
            ->andWhere('l.allowSubmit = 1')
            ->getQuery()
            ->getResult();

        return $this->render('@DOMJudge/jury/print.html.twig', [
            'form' => $form->createView(),
            'languages' => $languages,
        ]);
    }
}
