<template>
  <el-row>
    <vue-table ref="table" :api-url="'/permissions'" show-paginate :search-model="searchModel" :fields="fields"
               :item-actions="itemActions" @table-action="tableActions">
      <template slot="header-title">
        <span>权限列表</span>
      </template>
      <template slot="header-button">

      </template>
      <template slot="search-items">
        <el-form-item label="请求方法" prop="name">
          <el-input
            placeholder="请求方法"
            v-model="searchModel.name">
          </el-input>
        </el-form-item>
        <el-form-item label="用户组" prop="guard_name">
          <el-select v-model="searchModel.guard_name">
            <el-option
              v-for="(item, index) in guard_options"
              :key="index"
              :label="item"
              :value="item">
            </el-option>
          </el-select>
        </el-form-item>
      </template>
    </vue-table>
    <el-dialog
      title="详情"
      :visible.sync="show_permission_dialog"
      :close-on-click-modal="false"
      width="60%"
      append-to-body
      center>
      <el-form ref="permissionForm" :model="permissionModel" :rules="permissionRules"
               label-width="120px" style="max-width: 960px;">
        <div class="block-list">
          <div class="block-list-content row-one">
            <div class="item-row">
              <label>请求方法</label>
              <div class="value">{{permissionModel.name}}</div>
            </div>
          </div>
        </div>
        <el-form-item label="别名" prop="alias">
          <el-input v-model="permissionModel.alias"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer">
        <el-button type="primary" @click="permissionSubmit('permissionForm')">提交</el-button>
      </div>
    </el-dialog>
  </el-row>
</template>

<script>
  export default {
    name: "Permissions",
    data() {
      return {
        searchModel: {
          name: '',
          guard_name: '',
        },
        fields: [
          {
            label: '#',
            key: 'id',
            sortable: true,
            width: 80
          },
          {
            label: '请求方法',
            key: 'name',
          },
          {
            label: '别名',
            template: ({name, alias}) => {
              return alias ? alias : name
            }
          },
          {
            label: '用户组',
            key: 'guard_name',
          },
          {
            label: '创建时间',
            key: 'created_at',
          },
        ],
        itemActions: [
          {
            action: 'edit',
            label: '编辑',
          },
        ],
        guard_options: [],
        permissionModel: {
          id: '',
          name: '',
          guard_name: '',
          alias: '',
        },
        permissionRules: {
          alias: [
            {required: true, message: '请输入别名', trigger: 'blur'}
          ],
        },
        show_permission_dialog: false,
      }
    },
    created() {

    },
    mounted() {
      this.getGuardOptions()
    },
    methods: {
      getGuardOptions() {
        let that = this
        that.axios.get('/option/auth/guards').then(res => {
          that.guard_options = res
        })
      },
      tableActions(action, item) {
        let that = this
        that[action + 'Action'](item)
      },
      editAction(item) {
        let that = this
        that.permissionModel = item
        that.show_permission_dialog = true
      },
      permissionSubmit(ref) {
        let that = this
        that.$refs[ref].validate((valid) => {
          if (!valid) {
            console.log("submit error")
            return false
          }
          that.axios.post('/permissions', that.permissionModel).then(res => {
            that.$message.success("提交成功")
            that.show_permission_dialog = false
            that.$refs.table.loadData()
          })
        })
      },
    },
  }
</script>

<style scoped>
  .block-list .block-list-content {
    padding: 0;
  }

  .block-list .block-list-content .item-row label {
    width: 120px;
    padding: 0 12px 0 0;
    box-sizing: border-box;
  }
</style>
