import React from 'react';

import { Routes, Route } from 'react-router-dom';
import Dashboard from './components/Dashboard';
// import Listing from './Listing';
import { ToastProvider } from './hooks/useToast';
// import './styles/App.scss';

function App() {
    return (
        <ToastProvider>
            <div className="app">
                <Routes>
                    <Route path="/" element={<Dashboard />} />
                </Routes>
            </div>
        </ToastProvider>
    );
}

export default App;
