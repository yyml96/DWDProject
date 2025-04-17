import React, { useEffect } from 'react';
import firebase from 'firebase/app';
import 'firebase/auth';
import * as firebaseui from 'firebaseui';

const firebaseConfig = {
  apiKey: "AIzaSyBoQxesXRTWn7AanNw1N7LRP5QDIKE3Xo0",
  authDomain: "dynamic-qc.firebaseapp.com",
  projectId: "dynamic-qc",
  storageBucket: "dynamic-qc.appspot.com",
  messagingSenderId: "5945927102",
  appId: "1:5945927102:web:f9625c7ebcb25804f8c5ef"
};

if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
  }

const FirebaseAuth = () => {
  useEffect(() => {
    // Initialize the FirebaseUI Widget using Firebase
    const ui = new firebaseui.auth.AuthUI(firebase.auth());

    const uiConfig = {
      signInSuccessUrl: '/index', // Redirect after sign-in
      signInOptions: [
        firebase.auth.EmailAuthProvider.PROVIDER_ID,
      ],
    };

    ui.start('#firebaseui-auth-container', uiConfig);

    return () => ui.delete();
  }, []);

  return (
    <div>
      <h2>Sign In</h2>
      <div id="firebaseui-auth-container"></div>
    </div>
  );
};

export default FirebaseAuth;