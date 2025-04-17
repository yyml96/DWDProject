import { useMediaQuery, Theme } from "@mui/material";
import { List, SimpleList, Datagrid, TextField, EmailField, Edit, Create, SimpleForm, TextInput, Show, SimpleShowLayout, DateField, SelectInput, EditButton } from "react-admin";

export const UserList = () => {
    const isSmall = useMediaQuery<Theme>((theme) => theme.breakpoints.down("sm"));
    return (
        <List>
            {isSmall ? (
                <SimpleList
                    primaryText={(record) => record.name}
                    secondaryText={(record) => record.username}
                    tertiaryText={(record) => record.email}
                    linkType="show"
                />
            ) : (
                <Datagrid>
                    <TextField source="name" />
                    <TextField source="username" />
                    <EmailField source="email" />
                    <TextField source="phone" />
                    <TextField source="role" />
                    <EditButton />
                </Datagrid>
            )}
        </List>
    );
};

export const UserEdit = () => (
    <Edit>
        <SimpleForm>
            <TextInput source="name" />
            <TextInput source="username" />
            <TextInput source="email" />
            <TextInput source="phone" />
            <SelectInput source="role" choices={[
                { id: 'admin', name: 'Admin' },
                { id: 'site_builder', name: 'Site Builder' },
                { id: 'expert_reviewer', name: 'Expert Reviewer' },
            ]} />
        </SimpleForm>
    </Edit>
);

export const UserCreate = () => (
    <Create>
        <SimpleForm>
            <TextInput source="name" />
            <TextInput source="username" />
            <TextInput source="email" />
            <TextInput source="phone" />
            <SelectInput source="role" choices={[
                { id: 'admin', name: 'Admin' },
                { id: 'site_builder', name: 'Site Builder' },
                { id: 'expert_reviewer', name: 'Expert Reviewer' },
            ]} />
        </SimpleForm>
    </Create>
);

export const UserShow = () => (
    <Show>
        <SimpleShowLayout>
            <TextField source="name" />
            <TextField source="username" />
            <TextField source="email" />
            <TextField source="phone" />
            <TextField source="role" />
            <DateField source="created_at" />
        </SimpleShowLayout>
    </Show>
);