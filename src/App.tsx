//https://marmelab.com/react-admin/Tutorial.html
//https://firebase.google.com/docs/auth/web/firebaseui
import { Admin, radiantDarkTheme, radiantLightTheme, Resource } from "react-admin";
import { UserList, UserCreate, UserEdit, UserShow } from "./users";
import { TodoList, TodoShow } from "./todos";
import { PostList, PostEdit, PostCreate, PostShow } from "./posts";
import { Dashboard } from "./Dashboard";
import { LogList, LogShow } from "./logs";
import { SettingsPage } from "./settings";
import { authProvider } from "./authProvider";
import PostIcon from "@mui/icons-material/Book";
import UserIcon from "@mui/icons-material/Group";
import TudoIcon from "@mui/icons-material/Assignment";
import LogIcon from "@mui/icons-material/History";
import { dataProvider } from "./dataProvider";
import { getAuth, onAuthStateChanged } from "firebase/auth";
import { useEffect, useState } from "react";

type UserRole = 'admin' | 'site_builder' | 'expert_reviewer' | null;

export const App = () => {
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

  return (
    <Admin
      authProvider={authProvider}
      dataProvider={dataProvider}
      dashboard={Dashboard}
      theme={radiantLightTheme}
      darkTheme={radiantDarkTheme}
    >
      {userRole === 'admin' && (
        <>
          <Resource
            name="posts"
            list={PostList}
            show={PostShow}
            edit={PostEdit}
            create={PostCreate}
            icon={PostIcon}
          />

          <Resource
            name="todos"
            list={TodoList}
            show={TodoShow}
            icon={TudoIcon}
          />

          <Resource
            name="users"
            list={UserList}
            edit={UserEdit}
            create={UserCreate}
            show={UserShow}
            icon={UserIcon}
          />

          <Resource
            name="logs"
            list={LogList}
            show={LogShow}
            icon={LogIcon}
          />

          <Resource
            name="settings"
            options={{ label: 'Settings' }} 
            list={SettingsPage}
          />
        </>
      )}

      {userRole === 'site_builder' && (
        <Resource
          name="posts"
          list={PostList}
          show={PostShow}
          create={PostCreate}
          icon={PostIcon}
        />

      )}

      {userRole === 'expert_reviewer' && (
        <>
          <Resource
            name="posts"
            list={PostList}
            show={PostShow}
            edit={PostEdit}
            icon={PostIcon}
          />

          <Resource
            name="todos"
            list={TodoList}
            show={TodoShow}            
            icon={TudoIcon}
          />

        </>
      )}
    </Admin>
  );
};
