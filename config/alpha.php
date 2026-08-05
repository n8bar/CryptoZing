<?php

return [
    // Invite-only alpha gate: when on, new registrations land pending and
    // cannot log in until approved from the support dashboard (MS20 Phase 1).
    'gate_enabled' => (bool) env('ALPHA_GATE_ENABLED', true),
];
