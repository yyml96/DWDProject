import { Card, CardContent, CardHeader } from "@mui/material";
import { useEffect, useState } from "react";
import { getAuth, onAuthStateChanged } from "firebase/auth";

type UserRole = 'admin' | 'site_builder' | 'expert_reviewer' | null;

export const Dashboard = () => {
    const [userRole, setUserRole] = useState<UserRole>(null);

    useEffect(() => {
        const auth = getAuth();
        const unsubscribe = onAuthStateChanged(auth, async (user) => {
            if (user) {
                const uid = user.uid;
                try {
                    const response = await fetch(`http://localhost:8098/backend/api/getUserRole?uid=${uid}`);
                    const data = await response.json();
                    if (data.role) {
                        setUserRole(data.role);
                    }
                } catch (error) {
                    console.error("Error fetching user role:", error);
                }
            }
        });
        return () => unsubscribe();
    }, []);

    const renderContent = () => {
        switch (userRole) {
            case 'admin':
                return <div>Admin Dashboard: You can manage all tasks and users.</div>;
            case 'site_builder':
                return <div>Site Builder Dashboard: Mingalarpar.</div>;
            case 'expert_reviewer':
                return <div>Expert Reviewer Dashboard: You can assign tasks and review items.</div>;
            default:
                return <div>Welcome! Please log in to see your dashboard.</div>;
        }
    };

    return (
        <Card>
            <CardHeader title="Welcome to the team 'CodeFusion'" />
            <CardContent>{renderContent()}</CardContent>
        </Card>
    );
};
