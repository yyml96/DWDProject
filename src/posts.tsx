import { useMediaQuery, Theme } from "@mui/material";
import { List, Datagrid, TextField, ReferenceField, EditButton, SimpleForm, TextInput, ReferenceInput, Edit, Create, SimpleList, FileInput, FileField, CreateProps, Show, SimpleShowLayout, DateField, RichTextField, ImageField, ImageInput, useRecordContext, FunctionField, TabbedForm } from "react-admin";
import { AssignButton } from "./backend/Buttons";
import ImageRestore from "./backend/ImageRestore";
import VideoOverlayView from "./backend/VideoRestore";

const postFilters = [
    <TextInput source="q" label="Search" alwaysOn />,
    <ReferenceInput source="userId" label="User" reference="users" />,
];

export const PostList = () => {
    const userRole = localStorage.getItem('userRole');
    const isSmall = useMediaQuery<Theme>((theme) => theme.breakpoints.down("sm"));
    return (
        <List filters={postFilters}>
            {isSmall ? (
                <SimpleList
                    primaryText={(record) => record.id}
                    secondaryText={(record) => record.title}
                    linkType="show"
                />
            ) : (
                <Datagrid rowClick="show">
                    <ReferenceField source="userId" reference="users" link="show" />
                    <TextField source="title" />
                    <ReferenceField source="assignedTo" reference="users" link="show" />
                    <FunctionField
                        label="Assigned"
                        render={record =>
                            record.assignedTo ? 'Assigned' : 'Pending'
                        }
                    />
                    <AssignButton />
                    {userRole === 'admin' && <EditButton />}
                </Datagrid>
            )}
        </List>
    );
};

export const PostEdit = () => (
    <Edit>
        <SimpleForm>
            <TextInput source="id" InputProps={{ disabled: true }} />
            <ReferenceInput source="userId" reference="users" link="show" />
            <ReferenceInput source="assignedTo" reference="users" label="Assigned To" />
            <TextInput source="title" />
            <TextInput source="body" multiline rows={5} />
        </SimpleForm>
    </Edit>
);

export const PostCreate = (props: CreateProps) => {
    const userId = localStorage.getItem('userId');
    return (
        <Create {...props}>
            <SimpleForm>
                <TextInput source="userId" defaultValue={userId} disabled={true} />
                <TextInput source="title" />
                <TextInput source="body" multiline rows={5} />
                <ImageInput source="image" label="Upload Image" accept={{ 'image/*': [] }}>
                    <ImageField source="src" title="title" />
                </ImageInput>
                <FileInput source="video" label="Upload Video" accept={{ 'video/*': [] }}>
                    <FileField source="src" title="title" />
                </FileInput>
            </SimpleForm>
        </Create>
    );
};

const VideoField = (props) => {
    const record = useRecordContext(props);
    return <video src={`${record.videoUrl}`} controls width="320" height="240"></video>;
};

const ImgField = (props) => {
    const record = useRecordContext(props);
    return <img src={`${record.imageUrl}`} width="auto" height="auto"></img>;
};

export const PostShow = (props) => {
    const record = useRecordContext(props);

    return (
        <Show>
            <TabbedForm toolbar={false}>
                <TabbedForm.Tab label="Summary">
                    <SimpleShowLayout>
                        <ReferenceField source="userId" reference="users" link="show" />
                        <ReferenceField source="assignedTo" reference="users" link="show" />
                        <TextField source="title" />
                        <RichTextField source="body" />
                        <ImgField source="imageUrl" title="title" label="Image" />
                        <VideoField source="videoUrl" label="Video" />
                        <DateField source="createdAt" />
                    </SimpleShowLayout>
                </TabbedForm.Tab>
                <TabbedForm.Tab label="Image Comments">
                    <ImageRestore source="id" label="Image" />
                </TabbedForm.Tab>
                <TabbedForm.Tab label="Video Comments">
                    <VideoOverlayView source="id" label="Video" />
                </TabbedForm.Tab>
                    
            </TabbedForm>
        </Show>
    );
};
