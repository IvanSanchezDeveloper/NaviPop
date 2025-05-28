import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'

import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage.jsx';
import LoginCallback from './pages/LoginCallback';
import RequireAuth from './components/RequireAuthWrapper'
import AppLayout from './layouts/AppLayout.jsx'

function App() {
  return (
      <Router>
          <Routes>
              <Route element={<AppLayout />}>
                {/* Public route */}
                <Route path="/" element={<LoginPage/>}/>
                <Route path="/login" element={<LoginPage/>}/>
                <Route path="/register" element={<RegisterPage/>}/>
                <Route path="/login/callback" element={<LoginCallback/>}/>

                {/* Protected routes */}
                <Route element={<RequireAuth/>}>

                </Route>

                {/* Catch all for undefined routes */}
                <Route path="*" element={<Navigate to="/" replace/>}/>
              </Route>
          </Routes>
      </Router>
  )
}

export default App
