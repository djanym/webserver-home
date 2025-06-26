import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './app/App';
// import reportWebVitals from './reportWebVitals';
// import { REACT_APP_API_URL } from './env.js';
// import { config } from '../../config/config.js';

import './styles/index.scss';
import { BrowserRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';

const queryClient = new QueryClient();

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
    <React.StrictMode>
        <BrowserRouter>
            <QueryClientProvider client={queryClient}>
                <App />
            </QueryClientProvider>
        </BrowserRouter>
    </React.StrictMode>
);

// reportWebVitals();
