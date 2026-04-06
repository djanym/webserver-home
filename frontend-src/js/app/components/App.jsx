/**
 * Main app loader and shell wiring.
 */

import React, { useState } from 'react';
import AppShell from './AppShell';
import ProjectsModule from '../modules/projects/projects';

const App = () => {
    const [headerAction, setHeaderAction] = useState(null);

    return (
        <AppShell title="Webserver Home Manager" headerAction={headerAction}>
            <ProjectsModule setHeaderAction={setHeaderAction} />
        </AppShell>
    );
};

export default App;
