import { useMediaQuery, Theme } from "@mui/material";
import {
    List,
    Datagrid,
    TextField,
    ReferenceField,
    TabbedForm,
    SimpleShowLayout, 
    ReferenceInput,
    TextInput,
    SimpleList,
    BooleanField,
    SelectInput,
    FunctionField, 
    Show,
    useRecordContext
} from "react-admin";
import ImageEditor from "./backend/ImageEditor";
import VideoEditor from "./backend/VideoEditor";

const todoFilters = [
    <SelectInput source="completed" choices={[
        { id: true, name: 'Completed' },
        { id: false, name: 'Not Completed' },
    ]} />,
    <ReferenceInput source="userId" reference="users" />,
];

export const TodoList = () => {
    const isSmall = useMediaQuery<Theme>((theme) => theme.breakpoints.down("sm"));
    return (
        <List filters={todoFilters}>
            {isSmall ? (
                <SimpleList
                    primaryText={(record) => record.title}
                    secondaryText={(record) => record.userId}
                    tertiaryText={(record) => record.completed}
                    linkType="show"
                />
            ) : (
                <Datagrid rowClick="show">
                    <TextField source="postId" label="Post ID" />
                    <ReferenceField source="postId" reference="posts" label="Title" link="show">
                        <TextField source="title" />
                    </ ReferenceField>
                    <ReferenceField source="reviewerId" reference="users" label="Reviewer" link="show">
                        <TextField source="name" />
                    </ReferenceField>
                    <FunctionField
                    label="Completed" 
                    render={record => (record.completed ? '✓' : 'X')}
                    />                    
                </Datagrid>
            )}
        </List>
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

export const TodoShow = () => {
    const record = useRecordContext();
    return (
    <Show>
        <TabbedForm toolbar={false}>
            <TabbedForm.Tab label="Summary">
                <SimpleShowLayout>
                    <TextField source="id" label="Todo ID"/>
                    <ReferenceField source="postId" reference="posts" label="Title" link="show">
                        <TextField source="title" />
                    </ReferenceField>
                    <ReferenceField source="reviewerId" reference="users" label="Reviewer">
                        <TextField source="name" />
                    </ReferenceField>
                    <ReferenceField source="postId" reference="posts" label="Body">
                        <TextField source="body" />
                    </ReferenceField>
                </SimpleShowLayout>
            </TabbedForm.Tab>
            <TabbedForm.Tab label="Image">
                <ImageEditor source="imageUrl" label="Image" />
            </TabbedForm.Tab>
            <TabbedForm.Tab label="Video">
                <VideoEditor />
            </TabbedForm.Tab>
        </TabbedForm>
    </Show>
    );
};