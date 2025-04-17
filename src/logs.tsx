import { List, 
    Datagrid, 
    TextField, 
    Show, 
    SimpleShowLayout, 
    ReferenceField, 
    RichTextField
} from "react-admin";

export const LogList = () => (
    <List>
        <Datagrid>
            <TextField source="id" label="Log ID" />
            <TextField source="resource" label="Resource" />
            <TextField source="action" />
            <ReferenceField source="user_id" reference="users" label="User ID" />
            <TextField source="record_id" label="Record ID" />
            <TextField source="created_at" label="Action Time" />
        </Datagrid>
    </List>
)

export const LogShow = () => (
    <Show>
        <SimpleShowLayout>
            <TextField source="id" label="Log ID" />
            <TextField source="resource" label="Resource" />
            <TextField source="action" />
            <ReferenceField source="user_id" reference="users" label="User ID" />
            <TextField source="record_id" label="Record ID" />
            <RichTextField source="changes" />
            <TextField source="created_at" label="Action Time" />
        </SimpleShowLayout>
    </Show>
)
