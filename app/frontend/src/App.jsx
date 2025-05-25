import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'

import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import LoginPage from './pages/LoginPage';
import LoginCallback from './pages/LoginCallback';
import RequireAuth from './components/RequireAuthWrapper'

function App() {
  return (
      <Router>
          <h1>App Loaded</h1>
          <Routes>
              {/* Public route */}
              <Route path="/" element={<LoginPage/>}/>
              <Route path="/login/callback" element={<LoginCallback/>}/>

              {/* Protected routes */}
              <Route element={<RequireAuth/>}>

              </Route>

              {/* Catch all for undefined routes */}
              <Route path="*" element={<Navigate to="/" replace/>}/>

          </Routes>
      </Router>
  )
}

export default App
