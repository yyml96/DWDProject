import { useState } from 'react';
import { SimpleForm, Button, NumberInput, useNotify } from 'react-admin';

export const SettingsPage = () => {
    const notify = useNotify();
    const [maxAssignedPosts, setMaxAssignedPosts] = useState(10);

    const handleSave = async (values) => {
        try {
            const response = await fetch(`http://localhost:8098/backend/api/settings/maxAssignedPosts`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ max_assigned_posts: values.maxAssignedPosts }),
            });

            if (!response.ok) {
                throw new Error('Failed to update settings');
            }

            notify('Settings updated successfully', { type: 'success' });
        } catch (error) {
            notify('Error saving settings', { type: 'error' });
        }
    };

    return (
        <SimpleForm onSubmit={handleSave}>
        <NumberInput
            source="maxAssignedPosts"
            value={maxAssignedPosts}
            onChange={e => setMaxAssignedPosts(Number(e.target.value))}
        />
        </SimpleForm>
    );
};
