<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\EventGroup;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\EventManager;
use Dompdf\Dompdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cursus_event")
 */
class EventController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;
    /** @var EventManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        EventManager $manager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'cursus_event';
    }

    public function getClass()
    {
        return Event::class;
    }

    public function getIgnore()
    {
        return ['list', 'copyBulk'];
    }

    protected function getDefaultHiddenFilters()
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();

            return [
                'organizations' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations()),
            ];
        }

        return [];
    }

    /**
     * @Route("/{workspace}", name="apiv2_cursus_event_list", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listAction(Request $request, $class = Event::class, Workspace $workspace = null): JsonResponse
    {
        $query = $request->query->all();
        $options = $this->options['list'];

        $query['hiddenFilters'] = $this->getDefaultHiddenFilters();
        if ($workspace) {
            $query['hiddenFilters']['workspace'] = $workspace->getUuid();
        }

        return new JsonResponse(
            $this->finder->search($class, $query, $options ?? [])
        );
    }

    /**
     * @Route("/public/{workspace}", name="apiv2_cursus_event_public", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listPublicAction(Request $request, Workspace $workspace = null): JsonResponse
    {
        $query = $request->query->all();
        $options = $this->options['list'];

        $query['hiddenFilters'] = $this->getDefaultHiddenFilters();
        $query['hiddenFilters']['registrationType'] = Session::REGISTRATION_PUBLIC;
        $query['hiddenFilters']['terminated'] = false;
        if ($workspace) {
            $query['hiddenFilters']['workspace'] = $workspace->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(Event::class, $query, $options ?? [])
        );
    }

    /**
     * @Route("/{id}/open", name="apiv2_cursus_event_open", methods={"GET"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function openAction(Event $sessionEvent): JsonResponse
    {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        $user = $this->tokenStorage->getToken()->getUser();
        $registrations = [];
        if ($user instanceof User) {
            $registrations = [
                'users' => $this->finder->search(EventUser::class, ['filters' => [
                    'user' => $user->getUuid(),
                    'event' => $sessionEvent->getUuid(),
                ]])['data'],
                'groups' => $this->finder->search(EventGroup::class, ['filters' => [
                    'user' => $user->getUuid(),
                    'event' => $sessionEvent->getUuid(),
                ]])['data'],
            ];
        }

        return new JsonResponse([
            'event' => $this->serializer->serialize($sessionEvent),
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/{id}/pdf", name="apiv2_cursus_event_download_pdf", methods={"GET"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function downloadPdfAction(Event $sessionEvent, Request $request): StreamedResponse
    {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        $domPdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        $domPdf->loadHtml($this->manager->generateFromTemplate($sessionEvent, $request->getLocale()));

        // Render the HTML as PDF
        $domPdf->render();

        return new StreamedResponse(function () use ($domPdf) {
            echo $domPdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($sessionEvent->getName()).'.pdf',
        ]);
    }

    /**
     * @Route("/{id}/self/register", name="apiv2_cursus_session_event_self_register", methods={"PUT"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfRegisterAction(Event $sessionEvent, User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        if (Session::REGISTRATION_PUBLIC !== $sessionEvent->getRegistrationType()) {
            throw new AccessDeniedException();
        }
        $this->manager->registerUserToSessionEvent($sessionEvent, $user);

        $eventsRegistration = [];
        $eventUsers = !is_null($user) ?
            $this->finder->fetch(
                EventUser::class,
                ['session' => $sessionEvent->getSession()->getUuid(), 'user' => $user->getUuid()]
            ) :
            [];

        foreach ($eventUsers as $eventUser) {
            $event = $eventUser->getSessionEvent();
            $set = $event->getEventSet();
            $eventsRegistration[$event->getUuid()] = true;

            if ($set) {
                $setName = $set->getName();

                if (!isset($eventsRegistration[$setName])) {
                    $eventsRegistration[$setName] = $set->getLimit();
                }
                --$eventsRegistration[$setName];
            }
        }

        return new JsonResponse($eventsRegistration);
    }

    /**
     * @Route("/{id}/invite/all", name="apiv2_cursus_event_invite_all", methods={"PUT"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function inviteAllAction(Event $sessionEvent): JsonResponse
    {
        $this->checkPermission('EDIT', $sessionEvent, [], true);

        $this->manager->inviteAllSessionEventLearners($sessionEvent);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/users/{type}", name="apiv2_cursus_event_list_users", methods={"GET"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function listUsersAction(Event $sessionEvent, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['event'] = $sessionEvent->getUuid();
        $params['hiddenFilters']['type'] = $type;

        return new JsonResponse(
            $this->finder->search(EventUser::class, $params)
        );
    }

    /**
     * @Route("/{id}/users/{type}", name="apiv2_cursus_event_add_users", methods={"PATCH"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function addUsersAction(Event $sessionEvent, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $sessionEvent, [], true);

        $users = $this->decodeIdsString($request, User::class);
        $nbUsers = count($users);

        if (AbstractRegistration::LEARNER === $type && !$this->manager->checkSessionEventCapacity($sessionEvent, $nbUsers)) {
            return new JsonResponse(['errors' => [
                $this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus'),
            ]], 422); // not the best status (same as form validation errors)
        }

        $sessionEventUsers = $this->manager->addUsers($sessionEvent, $users, $type);

        return new JsonResponse(array_map(function (EventUser $sessionEventUser) {
            return $this->serializer->serialize($sessionEventUser);
        }, $sessionEventUsers));
    }

    /**
     * @Route("/{id}/users/{type}", name="apiv2_cursus_event_remove_users", methods={"DELETE"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function removeUsersAction(Event $sessionEvent, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $sessionEvent, [], true);

        $sessionEventUsers = $this->decodeIdsString($request, EventUser::class);
        $this->manager->removeUsers($sessionEventUsers);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/invite/users", name="apiv2_cursus_event_invite_users", methods={"PUT"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function inviteUsersAction(Event $sessionEvent, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $sessionEvent, [], true);

        $sessionUsers = $this->decodeIdsString($request, EventUser::class);
        $this->manager->sendSessionEventInvitation($sessionEvent, array_map(function (EventUser $sessionUser) {
            return $sessionUser->getUser();
        }, $sessionUsers));

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/groups/{type}", name="apiv2_cursus_event_list_groups", methods={"GET"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function listGroupsAction(Event $sessionEvent, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['event'] = $sessionEvent->getUuid();
        $params['hiddenFilters']['type'] = $type;

        return new JsonResponse(
            $this->finder->search(EventGroup::class, $params)
        );
    }

    /**
     * @Route("/{id}/groups/{type}", name="apiv2_cursus_event_add_groups", methods={"PATCH"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function addGroupsAction(Event $sessionEvent, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $sessionEvent, [], true);

        $groups = $this->decodeIdsString($request, Group::class);
        $nbUsers = 0;

        foreach ($groups as $group) {
            $nbUsers += count($group->getUsers()->toArray());
        }

        if (AbstractRegistration::LEARNER === $type && !$this->manager->checkSessionEventCapacity($sessionEvent, $nbUsers)) {
            return new JsonResponse(['errors' => [
                $this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus'),
            ]], 422); // not the best status (same as form validation errors)
        }

        $sessionGroups = $this->manager->addGroups($sessionEvent, $groups, $type);

        return new JsonResponse(array_map(function (EventGroup $sessionGroup) {
            return $this->serializer->serialize($sessionGroup);
        }, $sessionGroups));
    }

    /**
     * @Route("/{id}/groups/{type}", name="apiv2_cursus_event_remove_groups", methods={"DELETE"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function removeGroupsAction(Event $sessionEvent, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $sessionEvent, [], true);

        $sessionGroups = $this->decodeIdsString($request, EventGroup::class);
        $this->manager->removeGroups($sessionEvent, $sessionGroups);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/invite/groups", name="apiv2_cursus_event_invite_groups", methods={"PUT"})
     * @EXT\ParamConverter("sessionEvent", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function inviteGroupsAction(Event $sessionEvent, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $sessionEvent, [], true);

        $sessionGroups = $this->decodeIdsString($request, EventGroup::class);
        $users = [];
        foreach ($sessionGroups as $sessionGroup) {
            $groupUsers = $sessionGroup->getGroup()->getUsers();

            foreach ($groupUsers as $user) {
                $users[$user->getUuid()] = $user;
            }
        }

        $this->manager->sendSessionEventInvitation($sessionEvent, $users);

        return new JsonResponse(null, 204);
    }
}
