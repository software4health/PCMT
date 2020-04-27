<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\Ui\CategoryTreeController as OriginalCategoryTreeController;
use Akeneo\Platform\Bundle\UIBundle\Flash\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CategoryTreeController extends OriginalCategoryTreeController
{
    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request, $id): Response
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_edit'))) {
            throw new AccessDeniedException();
        }
        $category = $this->findCategory($id);
        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
                $this->addFlash('success', $message);
            }
        }

        return $this->render(
            sprintf('AkeneoPimEnrichmentBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(), //@todo - override the form so it contains permission handling
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        );
    }
}