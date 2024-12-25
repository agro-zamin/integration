<?php

namespace AgroZamin\Integration;

interface WithIntegrationInterface {
    /**
     * @param Integration $integration
     * @return $this
     */
    public function withIntegration(Integration $integration): static;
}