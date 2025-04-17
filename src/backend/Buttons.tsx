import { useState } from 'react';
import { Button as RaButton, useNotify, useRefresh, useRecordContext } from 'react-admin';
import PersonAddIcon from '@mui/icons-material/PersonAdd';

/**
 * AssignButton - Assign post to current reviewer
 */
const AssignButton = () => {
    const record = useRecordContext();
    const notify = useNotify();
    const refresh = useRefresh();
    const userRole = localStorage.getItem('userRole');
    const userId = localStorage.getItem('userId');
    const [maxAssignedPosts] = useState(0);

    const fetchMaxAssignedPosts = async () => {
        try {
            const response = await fetch('http://localhost:8098/backend/api/settings/maxAssignedPosts');
            if (!response.ok) {
                throw new Error('Failed to fetch max assigned posts');
            }
            const data = await response.json();
            return data.maxAssignedPosts;
        } catch (error) {
            notify(`Error: ${error.message}`, { type: 'error' });
        }
    };

    const fetchCurrentAssignedCount = async () => {
        try {
            const response = await fetch(`http://localhost:8098/backend/api/posts/assignedTo?assignedTo=${userId}`);
            if (!response.ok) {
                throw new Error('Failed to fetch current assigned count');
            }
            const data = await response.json();
            return data.postCount;
        } catch (error) {
            notify(`Error: ${error.message}`, { type: 'error' });
        }
    };

    const assignToReviewer = async (postId, reviewerId) => {
        try {
            const response = await fetch(`http://localhost:8098/backend/api/posts/assign/${postId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reviewerId }),
            });

            if (!response.ok) {
                throw new Error('Failed to assign post');
            }

            notify('Post assigned successfully', { type: 'success' });
        } catch (error) {
            notify(`Error: ${error.message}`, { type: 'error' });
        }
    };

    const handleAssign = async (event) => {
        event.preventDefault();

        const maxPosts = await fetchMaxAssignedPosts();
        const currentCount = await fetchCurrentAssignedCount();

        if (currentCount >= maxPosts) {
            notify(`You cannot assign more than ${maxAssignedPosts} posts`, { type: 'warning' });
            return;
        }

        try {
            await assignToReviewer(record.id, userId); // 传递 postId 和 reviewerId
            refresh();
        } catch (error) {
            notify(`Error: ${error.message}`, { type: 'error' });
        }
    };

    if (userRole !== 'expert_reviewer') {
        return null;
    }

    return !record.assignedTo ? (
        <RaButton type="button" onClick={handleAssign} label="Assign to me">
            <PersonAddIcon />
        </RaButton>
    ) : null;
};

export { AssignButton };
